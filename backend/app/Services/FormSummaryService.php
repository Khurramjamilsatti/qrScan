<?php

namespace App\Services;

use App\Models\Form;
use Illuminate\Support\Collection;

class FormSummaryService
{
    public function build(Form $form, Collection $submissions): array
    {
        $summary = [];

        foreach ($form->inputFields() as $field) {
            $fieldId = $field['id'];
            $type = $field['type'] ?? 'short_text';
            $responses = $submissions->map(fn ($s) => $s->data[$fieldId] ?? null)
                ->filter(fn ($v) => $v !== null && $v !== '' && $v !== []);

            $entry = [
                'id' => $fieldId,
                'title' => $field['title'] ?? '',
                'type' => $type,
                'total_responses' => $responses->count(),
            ];

            if (in_array($type, ['multiple_choice', 'dropdown', 'checkboxes', 'linear_scale', 'rating'], true)) {
                $counts = [];
                foreach ($responses as $response) {
                    if (is_array($response)) {
                        foreach ($response as $item) {
                            $counts[$item] = ($counts[$item] ?? 0) + 1;
                        }
                    } else {
                        $counts[$response] = ($counts[$response] ?? 0) + 1;
                    }
                }
                arsort($counts);
                $entry['distribution'] = $counts;

                if ($type === 'rating' && $responses->count() > 0) {
                    $numeric = $responses->map(fn ($v) => (float) $v)->filter();
                    $entry['average'] = round($numeric->avg(), 1);
                }
            } elseif (in_array($type, ['short_text', 'paragraph', 'email', 'url', 'number'], true)) {
                $entry['recent'] = $responses->take(10)->values()->all();
            }

            $summary[] = $entry;
        }

        return $summary;
    }
}
