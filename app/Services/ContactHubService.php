<?php

namespace App\Services;

use App\Models\Contact;

class ContactHubService
{
    public function syncContactDetails(Contact $contact): void
    {
        $this->updateBeliefAutoUpdate($contact);
        $this->extractPreferences($contact);
        $this->updateEmotionalBaseline($contact);
    }

    public function updateBeliefAutoUpdate(Contact $contact): void
    {
        $metadata = $contact->metadata ?? [];
        $attributes = $contact->attributes ?? [];

        if (!empty($metadata['changed_by'])) {
            $contact->metadata = array_merge($metadata, [
                'beliefs' => array_merge($metadata['beliefs'] ?? [], [
                    'last_synced_by' => $metadata['changed_by'],
                    'updated_at' => now()->toDateTimeString(),
                ]),
            ]);
        }

        if (!isset($contact->metadata['beliefs'])) {
            $contact->metadata = array_merge($contact->metadata ?? [], [
                'beliefs' => [
                    'confidence' => 0.5,
                    'source' => 'system',
                ],
            ]);
        }

        $contact->save();
    }

    public function extractPreferences(Contact $contact): void
    {
        $attributes = $contact->attributes ?? [];

        if (!isset($attributes['preferences'])) {
            $attributes['preferences'] = [
                'communication' => 'default',
                'timezone' => $attributes['timezone'] ?? 'UTC',
            ];

            $contact->attributes = $attributes;
            $contact->save();
        }
    }

    public function buildRelationshipGraph(Contact $contact): array
    {
        $connections = [];

        foreach ($contact->rules as $rule) {
            if (!empty($rule->metadata['related_contact_id'])) {
                $connections[] = [
                    'source' => $contact->id,
                    'target' => $rule->metadata['related_contact_id'],
                    'relationship' => $rule->metadata['relationship'] ?? 'related',
                ];
            }
        }

        return [
            'contact' => $contact->id,
            'nodes' => [
                ['id' => $contact->id, 'label' => $contact->name],
            ],
            'edges' => $connections,
        ];
    }

    public function updateEmotionalBaseline(Contact $contact): void
    {
        $notes = $contact->notes()->pluck('note')->toArray();

        if (count($notes) === 0) {
            $baseline = 'neutral';
        } else {
            $baseline = collect($notes)->reduce(function ($carry, $note) {
                if (str_contains(strtolower($note), 'happy')) {
                    return 1;
                }

                if (str_contains(strtolower($note), 'angry') || str_contains(strtolower($note), 'frustrated')) {
                    return -1;
                }

                return $carry;
            }, 0);

            $baseline = match (true) {
                $baseline > 0 => 'positive',
                $baseline < 0 => 'negative',
                default => 'neutral',
            };
        }

        $contact->metadata = array_merge($contact->metadata ?? [], [
            'emotional_baseline' => $baseline,
            'emotion_updated_at' => now()->toDateTimeString(),
        ]);
        $contact->save();
    }

    public function getContactAnalytics(Contact $contact): array
    {
        return [
            'type' => $contact->type,
            'last_seen_at' => optional($contact->last_seen_at)->toDateTimeString(),
            'memory_count' => $contact->memories()->count(),
            'tag_count' => $contact->tags()->count(),
            'rule_count' => $contact->rules()->count(),
            'baseline' => $contact->metadata['emotional_baseline'] ?? 'neutral',
        ];
    }
}
