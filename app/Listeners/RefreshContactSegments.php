<?php

namespace App\Listeners;

use App\Events\ContactCreated;
use App\Events\ContactUpdated;
use App\Jobs\RefreshDynamicSegmentsJob;
use App\Models\ContactSegment;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class RefreshContactSegments implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle($event): void
    {
        try {
            $contact = $this->getContactFromEvent($event);
            
            if (!$contact) {
                return;
            }

            // Refresh segments for the specific contact
            $this->refreshSegmentsForContact($contact);

            // If contact was updated, check if we need to refresh all dynamic segments
            if ($event instanceof ContactUpdated) {
                $this->refreshDynamicSegmentsIfNeeded($contact, $event->changes);
            }

            Log::debug('Contact segments refreshed successfully', [
                'contact_id' => $contact->id,
                'event' => get_class($event),
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to refresh contact segments', [
                'error' => $e->getMessage(),
                'event' => get_class($event),
                'contact_id' => $this->getContactFromEvent($event)?->id,
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Get contact from event.
     */
    private function getContactFromEvent($event)
    {
        if ($event instanceof ContactCreated || $event instanceof ContactUpdated) {
            return $event->contact;
        }

        return null;
    }

    /**
     * Refresh segments for a specific contact.
     */
    private function refreshSegmentsForContact($contact): void
    {
        // Get all dynamic segments
        $dynamicSegments = ContactSegment::where('type', 'dynamic')
            ->where('is_active', true)
            ->get();

        foreach ($dynamicSegments as $segment) {
            try {
                $shouldBeMember = $this->evaluateSegmentConditions($contact, $segment);
                $isMember = $segment->contacts()->where('contact_id', $contact->id)->exists();

                if ($shouldBeMember && !$isMember) {
                    // Add contact to segment
                    $segment->contacts()->attach($contact->id, [
                        'added_at' => now(),
                        'source' => 'automatic',
                    ]);
                    
                    $segment->increment('contact_count');
                    
                    Log::info('Contact added to segment', [
                        'contact_id' => $contact->id,
                        'segment_id' => $segment->id,
                        'segment_name' => $segment->name,
                    ]);
                    
                } elseif (!$shouldBeMember && $isMember) {
                    // Remove contact from segment
                    $segment->contacts()->detach($contact->id);
                    
                    $segment->decrement('contact_count');
                    
                    Log::info('Contact removed from segment', [
                        'contact_id' => $contact->id,
                        'segment_id' => $segment->id,
                        'segment_name' => $segment->name,
                    ]);
                }

            } catch (\Exception $e) {
                Log::error('Failed to refresh contact segment', [
                    'error' => $e->getMessage(),
                    'contact_id' => $contact->id,
                    'segment_id' => $segment->id,
                    'segment_name' => $segment->name,
                ]);
            }
        }
    }

    /**
     * Evaluate if contact matches segment conditions.
     */
    private function evaluateSegmentConditions($contact, $segment): bool
    {
        if (!$segment->conditions) {
            return false;
        }

        try {
            $conditions = json_decode($segment->conditions, true);
            
            if (!is_array($conditions) || empty($conditions)) {
                return false;
            }

            return $this->evaluateConditionsGroup($contact, $conditions);

        } catch (\Exception $e) {
            Log::error('Failed to evaluate segment conditions', [
                'error' => $e->getMessage(),
                'contact_id' => $contact->id,
                'segment_id' => $segment->id,
                'conditions' => $segment->conditions,
            ]);
            
            return false;
        }
    }

    /**
     * Evaluate a group of conditions with logic operators.
     */
    private function evaluateConditionsGroup($contact, array $conditions): bool
    {
        $logic = $conditions['logic'] ?? 'and';
        $rules = $conditions['rules'] ?? [];

        if (empty($rules)) {
            return false;
        }

        $results = [];

        foreach ($rules as $rule) {
            if (isset($rule['rules'])) {
                // Nested condition group
                $results[] = $this->evaluateConditionsGroup($contact, $rule);
            } else {
                // Individual condition
                $results[] = $this->evaluateSingleCondition($contact, $rule);
            }
        }

        return $logic === 'or' ? in_array(true, $results) : !in_array(false, $results);
    }

    /**
     * Evaluate a single condition.
     */
    private function evaluateSingleCondition($contact, array $condition): bool
    {
        $field = $condition['field'] ?? '';
        $operator = $condition['operator'] ?? '';
        $value = $condition['value'] ?? '';

        if (empty($field) || empty($operator)) {
            return false;
        }

        $contactValue = $this->getContactFieldValue($contact, $field);

        return $this->applyOperator($contactValue, $operator, $value);
    }

    /**
     * Get contact field value.
     */
    private function getContactFieldValue($contact, string $field)
    {
        // Handle nested fields (e.g., created_at.date)
        if (str_contains($field, '.')) {
            [$mainField, $modifier] = explode('.', $field, 2);
            $value = $contact->getAttribute($mainField);
            
            if ($modifier === 'date' && $value instanceof \Carbon\Carbon) {
                return $value->format('Y-m-d');
            }
            
            return $value;
        }

        return $contact->getAttribute($field);
    }

    /**
     * Apply operator to compare values.
     */
    private function applyOperator($contactValue, string $operator, $conditionValue): bool
    {
        return match ($operator) {
            'equals' => $contactValue == $conditionValue,
            'not_equals' => $contactValue != $conditionValue,
            'contains' => str_contains(strtolower($contactValue ?? ''), strtolower($conditionValue)),
            'not_contains' => !str_contains(strtolower($contactValue ?? ''), strtolower($conditionValue)),
            'starts_with' => str_starts_with(strtolower($contactValue ?? ''), strtolower($conditionValue)),
            'ends_with' => str_ends_with(strtolower($contactValue ?? ''), strtolower($conditionValue)),
            'greater_than' => $contactValue > $conditionValue,
            'less_than' => $contactValue < $conditionValue,
            'greater_than_or_equal' => $contactValue >= $conditionValue,
            'less_than_or_equal' => $contactValue <= $conditionValue,
            'is_empty' => empty($contactValue),
            'is_not_empty' => !empty($contactValue),
            'in' => is_array($conditionValue) ? in_array($contactValue, $conditionValue) : false,
            'not_in' => is_array($conditionValue) ? !in_array($contactValue, $conditionValue) : true,
            default => false,
        };
    }

    /**
     * Check if we need to refresh all dynamic segments.
     */
    private function refreshDynamicSegmentsIfNeeded($contact, array $changes): void
    {
        // Fields that commonly affect segment conditions
        $significantFields = [
            'status', 'email', 'phone', 'company', 'location', 
            'engagement_score', 'last_activity', 'source', 'tags'
        ];

        $hasSignificantChanges = !empty(array_intersect(array_keys($changes), $significantFields));

        if ($hasSignificantChanges) {
            // Dispatch job to refresh all dynamic segments
            RefreshDynamicSegmentsJob::dispatch()->delay(now()->addMinutes(1));
            
            Log::info('Dynamic segments refresh job queued due to significant contact changes', [
                'contact_id' => $contact->id,
                'changed_fields' => array_keys($changes),
            ]);
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed($event, $exception): void
    {
        Log::error('RefreshContactSegments listener failed', [
            'event' => get_class($event),
            'exception' => $exception->getMessage(),
            'contact_id' => $this->getContactFromEvent($event)?->id,
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}
