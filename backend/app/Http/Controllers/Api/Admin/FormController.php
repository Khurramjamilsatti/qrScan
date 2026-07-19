<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Form;
use App\Models\FormSubmission;
use App\Services\FormSummaryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FormController extends Controller
{
    public function __construct(private FormSummaryService $summary) {}

    public function index(): JsonResponse
    {
        $forms = Form::query()
            ->with('user:id,name,email')
            ->orderByDesc('updated_at')
            ->paginate(30);

        return response()->json($forms);
    }

    public function show(Form $form): JsonResponse
    {
        $form->load('user:id,name,email');
        $allSubmissions = $form->submissions()->orderByDesc('created_at')->get();
        $submissions = $form->submissions()->orderByDesc('created_at')->paginate(50);

        return response()->json([
            'form' => $form,
            'submissions' => $submissions,
            'summary' => $this->summary->build($form, $allSubmissions),
        ]);
    }

    public function destroySubmission(Form $form, FormSubmission $submission): JsonResponse
    {
        if ($submission->form_id !== $form->id) {
            abort(404);
        }

        $submission->delete();
        $form->decrement('submission_count');

        return response()->json(['message' => __('messages.deleted')]);
    }
}
