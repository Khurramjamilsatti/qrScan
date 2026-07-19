<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Form;
use App\Models\FormSubmission;
use App\Services\AnalyticsService;
use App\Services\DomainUrlService;
use App\Services\FormSummaryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FormController extends Controller
{
    public function __construct(
        private DomainUrlService $domains,
        private AnalyticsService $analytics,
        private FormSummaryService $summary,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $items = $request->user()->forms()->with('customDomain')->latest()->get()
            ->map(fn ($f) => $this->enrich($f));

        return response()->json($items);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user->canCreate('forms')) {
            return response()->json(['message' => __('messages.form_limit_reached')], 403);
        }

        $validated = $this->validated($request);

        if (! empty($validated['custom_domain_id']) && ! $this->domains->canUseCustomDomains($user)) {
            return response()->json(['message' => __('messages.custom_domains_pro_required')], 403);
        }

        $form = $user->forms()->create($validated);

        return response()->json($this->enrich($form->load('customDomain')), 201);
    }

    public function show(Request $request, Form $form): JsonResponse
    {
        $this->authorizeOwner($request, $form);

        return response()->json($this->enrich($form->load(['customDomain', 'analyticsEvents'])));
    }

    public function update(Request $request, Form $form): JsonResponse
    {
        $this->authorizeOwner($request, $form);

        $form->update($this->validated($request, $form->id));

        return response()->json($this->enrich($form->fresh('customDomain')));
    }

    public function destroy(Request $request, Form $form): JsonResponse
    {
        $this->authorizeOwner($request, $form);
        $form->delete();

        return response()->json(['message' => __('messages.deleted')]);
    }

    public function togglePublish(Request $request, Form $form): JsonResponse
    {
        $this->authorizeOwner($request, $form);
        $form->update(['is_active' => ! $form->is_active]);

        return response()->json($this->enrich($form->fresh('customDomain')));
    }

    public function publicShow(Request $request, string $slug): JsonResponse
    {
        $form = Form::where('slug', $slug)->first();

        if (! $form) {
            abort(404, __('messages.form_not_found'));
        }

        if (! $form->is_active) {
            abort(403, __('messages.form_not_published'));
        }

        if ($form->user->canScan()) {
            $form->increment('view_count');
            $form->user->incrementScans();
            $this->analytics->track($form, 'view', $request, $form->user_id);
        }

        $payload = $this->publicPayload($form);

        if ($form->max_submissions_per_respondent > 0) {
            $ipHash = hash('sha256', $request->ip().'|'.$request->userAgent());
            $existing = FormSubmission::where('form_id', $form->id)
                ->where('ip_hash', $ipHash)
                ->count();

            if ($existing >= $form->max_submissions_per_respondent) {
                $settings = $form->settings ?? [];
                $payload['already_submitted'] = true;
                $payload['confirmation_message'] = $settings['confirmation_message']
                    ?? __('messages.form_default_confirmation');
            }
        }

        return response()->json($payload);
    }

    public function submit(Request $request, string $slug): JsonResponse
    {
        $form = Form::where('slug', $slug)->first();

        if (! $form) {
            abort(404, __('messages.form_not_found'));
        }

        if (! $form->is_active) {
            abort(403, __('messages.form_not_published'));
        }

        if (! $form->isAcceptingSubmissions()) {
            return response()->json(['message' => __('messages.form_closed')], 422);
        }

        $ipHash = hash('sha256', $request->ip().'|'.$request->userAgent());

        if ($form->max_submissions_per_respondent > 0) {
            $existing = FormSubmission::where('form_id', $form->id)
                ->where('ip_hash', $ipHash)
                ->count();

            if ($existing >= $form->max_submissions_per_respondent) {
                return response()->json(['message' => __('messages.form_already_submitted')], 429);
            }
        }

        $settings = $form->settings ?? [];
        $rules = [];
        $attributes = [];

        if (! empty($settings['collect_email'])) {
            $rules['respondent_email'] = 'required|email|max:255';
            $attributes['respondent_email'] = __('messages.form_email_label');
        }

        foreach ($form->inputFields() as $field) {
            $key = 'responses.'.$field['id'];
            $fieldRules = $this->fieldValidationRules($field);
            if (! empty($fieldRules)) {
                $rules[$key] = $fieldRules;
                $attributes[$key] = $field['title'] ?: $field['id'];
            }

            if (in_array($field['type'] ?? '', ['checkboxes', 'grid_checkbox'], true)) {
                $rules[$key.'.*'] = 'string|max:500';
            }
        }

        $validator = Validator::make($request->all(), $rules, [], $attributes);

        if ($validator->fails()) {
            return response()->json(['message' => __('messages.form_validation_failed'), 'errors' => $validator->errors()], 422);
        }

        $responses = $request->input('responses', []);

        $submission = FormSubmission::create([
            'form_id' => $form->id,
            'data' => $responses,
            'respondent_email' => $request->input('respondent_email'),
            'ip_hash' => $ipHash,
            'user_agent' => $request->userAgent(),
            'created_at' => now(),
        ]);

        $form->increment('submission_count');

        if ($form->user->canScan()) {
            $form->user->incrementScans();
            $this->analytics->track($form, 'submission', request(), $form->user_id);
        }

        return response()->json([
            'message' => $settings['confirmation_message'] ?? __('messages.form_default_confirmation'),
            'submission_id' => $submission->id,
            'show_submit_another' => ! empty($settings['show_submit_another']),
            'redirect_url' => $settings['redirect_url'] ?? '/app/forms',
        ], 201);
    }

    public function submissions(Request $request, Form $form): JsonResponse
    {
        $this->authorizeOwner($request, $form);

        $submissions = $form->submissions()->orderByDesc('created_at')->paginate(50);

        return response()->json([
            'submissions' => $submissions,
            'summary' => $this->summary->build($form, $form->submissions()->get()),
        ]);
    }

    public function destroySubmission(Request $request, Form $form, FormSubmission $submission): JsonResponse
    {
        $this->authorizeOwner($request, $form);

        if ($submission->form_id !== $form->id) {
            abort(404);
        }

        $submission->delete();
        $form->decrement('submission_count');

        return response()->json(['message' => __('messages.deleted')]);
    }

    public function exportSubmissions(Request $request, Form $form): StreamedResponse
    {
        $this->authorizeOwner($request, $form);

        $submissions = $form->submissions()->orderBy('created_at')->get();
        $fields = $form->inputFields();
        $filename = 'form-'.$form->slug.'-responses.csv';

        return response()->streamDownload(function () use ($submissions, $fields, $form) {
            $handle = fopen('php://output', 'w');
            $headers = ['Submitted At', 'Email'];
            foreach ($fields as $field) {
                $headers[] = $field['title'] ?? $field['id'];
            }
            fputcsv($handle, $headers);

            foreach ($submissions as $submission) {
                $row = [
                    $submission->created_at?->toDateTimeString() ?? '',
                    $submission->respondent_email ?? '',
                ];
                foreach ($fields as $field) {
                    $row[] = $this->formatCsvValue($submission->data[$field['id']] ?? null);
                }
                fputcsv($handle, $row);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    private function fieldValidationRules(array $field): array|string
    {
        $type = $field['type'] ?? 'short_text';
        $required = ! empty($field['required']);

        if (in_array($type, ['section_header', 'description_text'], true)) {
            return [];
        }

        $rules = [];

        if ($required) {
            $rules[] = 'required';
            if (in_array($type, ['checkboxes', 'grid_checkbox'], true)) {
                $rules[] = 'min:1';
            }
            if ($type === 'grid_multiple_choice') {
                $rules[] = 'min:1';
            }
        } else {
            $rules[] = 'nullable';
        }

        return match ($type) {
            'email' => array_merge($rules, ['email', 'max:255']),
            'number' => array_merge($rules, ['numeric']),
            'url' => array_merge($rules, ['string', 'max:2048']),
            'date' => array_merge($rules, ['date']),
            'time' => array_merge($rules, ['date_format:H:i']),
            'paragraph', 'short_text' => array_merge($rules, ['string', 'max:5000']),
            'multiple_choice', 'dropdown' => array_merge($rules, ['string', 'max:500']),
            'checkboxes', 'grid_checkbox' => array_merge($rules, ['array']),
            'linear_scale' => array_merge($rules, ['integer', 'min:'.($field['scale_min'] ?? 1), 'max:'.($field['scale_max'] ?? 5)]),
            'rating' => array_merge($rules, ['integer', 'min:1', 'max:'.($field['rating_max'] ?? 5)]),
            'grid_multiple_choice' => array_merge($rules, ['array']),
            default => array_merge($rules, ['string', 'max:5000']),
        };
    }

    private function formatCsvValue(mixed $value): string
    {
        if (is_array($value)) {
            return implode('; ', array_map('strval', $value));
        }

        return (string) ($value ?? '');
    }

    private function publicPayload(Form $form): array
    {
        return [
            'id' => $form->id,
            'slug' => $form->slug,
            'title' => $form->title,
            'description' => $form->description,
            'fields' => $form->fields,
            'settings' => $form->settings,
            'theme_color' => $form->theme_color,
            'background_color' => $form->background_color,
            'header_image_path' => $form->header_image_path,
            'logo_path' => $form->logo_path,
            'background_image_path' => $form->background_image_path,
            'is_accepting' => $form->isAcceptingSubmissions(),
            'closes_at' => $form->closes_at,
            'submission_count' => $form->submission_count,
            'max_submissions' => $form->max_submissions,
            'max_submissions_per_respondent' => $form->max_submissions_per_respondent,
        ];
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'slug' => ['required', 'string', 'alpha_dash', 'min:3', 'max:50', Rule::unique('forms')->ignore($ignoreId)],
            'title' => ($ignoreId ? 'sometimes|' : '').'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'fields' => 'nullable|array',
            'fields.*.id' => 'required_with:fields|string|max:50',
            'fields.*.type' => 'required_with:fields|string|max:50',
            'fields.*.title' => 'nullable|string|max:500',
            'fields.*.description' => 'nullable|string|max:2000',
            'fields.*.required' => 'nullable|boolean',
            'fields.*.options' => 'nullable|array',
            'fields.*.scale_min' => 'nullable|integer|min:0|max:10',
            'fields.*.scale_max' => 'nullable|integer|min:1|max:10',
            'fields.*.rating_max' => 'nullable|integer|min:3|max:10',
            'fields.*.rows' => 'nullable|array',
            'fields.*.columns' => 'nullable|array',
            'settings' => 'nullable|array',
            'settings.collect_email' => 'nullable|boolean',
            'settings.confirmation_message' => 'nullable|string|max:2000',
            'settings.redirect_url' => 'nullable|string|max:2048',
            'settings.show_progress_bar' => 'nullable|boolean',
            'settings.show_submit_another' => 'nullable|boolean',
            'settings.shuffle_questions' => 'nullable|boolean',
            'theme_color' => 'nullable|string|max:7',
            'background_color' => 'nullable|string|max:7',
            'header_image_path' => 'nullable|string|max:500',
            'logo_path' => 'nullable|string|max:500',
            'background_image_path' => 'nullable|string|max:500',
            'qr_shape' => 'nullable|in:square,rounded,circle,hexagon,diamond',
            'dot_style' => 'nullable|in:square,round,rounded,dots,classy,extra-rounded',
            'corner_style' => 'nullable|in:sharp,rounded,dot,extra-round',
            'frame_style' => 'nullable|in:none,simple,rounded,banner-top,banner-bottom,badge',
            'custom_domain_id' => 'nullable|exists:custom_domains,id',
            'is_active' => 'sometimes|boolean',
            'closes_at' => 'nullable|date',
            'max_submissions' => 'nullable|integer|min:0|max:1000000',
            'max_submissions_per_respondent' => 'nullable|integer|min:0|max:100',
        ]);
    }

    private function enrich(Form $form): Form
    {
        $form->setAttribute('form_url', $this->domains->formUrl(
            $form->user,
            $form->slug,
            $form->custom_domain_id
        ));
        $form->setAttribute('domain_label', $form->customDomain?->domain);

        return $form;
    }

    private function authorizeOwner(Request $request, Form $form): void
    {
        if ($form->user_id !== $request->user()->id) {
            abort(403);
        }
    }
}
