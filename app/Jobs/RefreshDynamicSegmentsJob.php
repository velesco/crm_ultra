<?php

namespace App\Jobs;

use App\Models\Contact;
use App\Models\ContactSegment;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RefreshDynamicSegmentsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public $tries = 2;

    /**
     * The maximum number of seconds the job should run.
     */
    public $timeout = 900; // 15 minutes

    /**
     * The segment to refresh (null for all dynamic segments).
     */
    protected ?ContactSegment $segment;

    /**
     * Create a new job instance.
     */
    public function __construct(?ContactSegment $segment = null)
    {
        $this->segment = $segment;
        $this->onQueue('segments');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Log::info('Starting dynamic segments refresh job');

            if ($this->segment) {
                // Refresh specific segment
                $this->refreshSegment($this->segment);
                Log::info("Refreshed dynamic segment: {$this->segment->name} (ID: {$this->segment->id})");
            } else {
                // Refresh all dynamic segments
                $segments = ContactSegment::where('type', 'dynamic')
                    ->where('is_active', true)
                    ->get();

                $refreshed = 0;
                foreach ($segments as $segment) {
                    try {
                        $this->refreshSegment($segment);
                        $refreshed++;
                    } catch (Exception $e) {
                        Log::error("Failed to refresh segment {$segment->id}: ".$e->getMessage());
                    }
                }

                Log::info("Refreshed {$refreshed} dynamic segments out of {$segments->count()}");
            }

        } catch (Exception $e) {
            Log::error('RefreshDynamicSegmentsJob failed: '.$e->getMessage());
            throw $e;
        }
    }

    /**
     * Refresh a specific dynamic segment.
     */
    protected function refreshSegment(ContactSegment $segment): void
    {
        if ($segment->type !== 'dynamic') {
            Log::info("Segment {$segment->id} is not dynamic, skipping");

            return;
        }

        if (! $segment->conditions) {
            Log::warning("Segment {$segment->id} has no conditions defined");

            return;
        }

        try {
            // Build query based on segment conditions
            $query = $this->buildSegmentQuery($segment->conditions);

            // Get contact IDs that match the conditions
            $matchingContactIds = $query->pluck('id')->toArray();

            // Get current members of the segment
            $currentMemberIds = $segment->contacts()->pluck('contact_id')->toArray();

            // Find contacts to add and remove
            $contactsToAdd = array_diff($matchingContactIds, $currentMemberIds);
            $contactsToRemove = array_diff($currentMemberIds, $matchingContactIds);

            DB::transaction(function () use ($segment, $contactsToAdd, $contactsToRemove) {
                // Remove contacts that no longer match
                if (! empty($contactsToRemove)) {
                    $segment->contacts()->detach($contactsToRemove);
                    Log::debug('Removed '.count($contactsToRemove)." contacts from segment {$segment->id}");
                }

                // Add new contacts that match
                if (! empty($contactsToAdd)) {
                    $attachData = [];
                    foreach ($contactsToAdd as $contactId) {
                        $attachData[$contactId] = [
                            'added_at' => now(),
                            'added_by' => null, // System added
                        ];
                    }
                    $segment->contacts()->attach($attachData);
                    Log::debug('Added '.count($contactsToAdd)." contacts to segment {$segment->id}");
                }

                // Update segment statistics
                $newContactCount = count($matchingContactIds);
                $segment->update([
                    'contact_count' => $newContactCount,
                    'last_refreshed_at' => now(),
                ]);
            });

            Log::info("Segment {$segment->id} refreshed: {$segment->contact_count} total contacts, ".
                     'added '.count($contactsToAdd).', removed '.count($contactsToRemove));

        } catch (Exception $e) {
            Log::error("Error refreshing segment {$segment->id}: ".$e->getMessage());
            throw $e;
        }
    }

    /**
     * Build query from segment conditions.
     */
    protected function buildSegmentQuery(array $conditions)
    {
        $query = Contact::where('is_active', true);

        if (empty($conditions)) {
            return $query;
        }

        // Handle grouped conditions with AND/OR logic
        $logic = $conditions['logic'] ?? 'and';
        $rules = $conditions['rules'] ?? [];

        if (empty($rules)) {
            return $query;
        }

        // Build where clauses based on conditions
        $query = $this->applyConditionGroup($query, $rules, $logic);

        return $query;
    }

    /**
     * Apply a group of conditions to the query.
     */
    protected function applyConditionGroup($query, array $rules, string $logic = 'and')
    {
        foreach ($rules as $index => $rule) {
            // If this rule has nested rules, handle as group
            if (isset($rule['rules'])) {
                $groupLogic = $rule['logic'] ?? 'and';

                if ($index === 0) {
                    $query->where(function ($subQuery) use ($rule, $groupLogic) {
                        $this->applyConditionGroup($subQuery, $rule['rules'], $groupLogic);
                    });
                } else {
                    if ($logic === 'and') {
                        $query->where(function ($subQuery) use ($rule, $groupLogic) {
                            $this->applyConditionGroup($subQuery, $rule['rules'], $groupLogic);
                        });
                    } else {
                        $query->orWhere(function ($subQuery) use ($rule, $groupLogic) {
                            $this->applyConditionGroup($subQuery, $rule['rules'], $groupLogic);
                        });
                    }
                }
            } else {
                // Handle single condition
                $this->applySingleCondition($query, $rule, $logic, $index === 0);
            }
        }

        return $query;
    }

    /**
     * Apply a single condition to the query.
     */
    protected function applySingleCondition($query, array $condition, string $logic, bool $isFirst)
    {
        $field = $condition['field'] ?? '';
        $operator = $condition['operator'] ?? '';
        $value = $condition['value'] ?? '';

        if (empty($field) || empty($operator)) {
            return $query;
        }

        // Determine the where method based on logic and position
        $whereMethod = $this->getWhereMethod($logic, $isFirst);

        switch ($operator) {
            case 'equals':
                $query->{$whereMethod}($field, '=', $value);
                break;

            case 'not_equals':
                $query->{$whereMethod}($field, '!=', $value);
                break;

            case 'contains':
                $query->{$whereMethod}($field, 'LIKE', "%{$value}%");
                break;

            case 'not_contains':
                $query->{$whereMethod}($field, 'NOT LIKE', "%{$value}%");
                break;

            case 'starts_with':
                $query->{$whereMethod}($field, 'LIKE', "{$value}%");
                break;

            case 'ends_with':
                $query->{$whereMethod}($field, 'LIKE', "%{$value}");
                break;

            case 'greater_than':
                $query->{$whereMethod}($field, '>', $value);
                break;

            case 'greater_than_equal':
                $query->{$whereMethod}($field, '>=', $value);
                break;

            case 'less_than':
                $query->{$whereMethod}($field, '<', $value);
                break;

            case 'less_than_equal':
                $query->{$whereMethod}($field, '<=', $value);
                break;

            case 'is_null':
                $query->{$whereMethod}($field, null);
                break;

            case 'is_not_null':
                $whereMethod = $logic === 'and' && $isFirst ? 'whereNotNull' :
                             ($logic === 'or' ? 'orWhereNotNull' : 'whereNotNull');
                $query->{$whereMethod}($field);
                break;

            case 'in':
                $values = is_array($value) ? $value : explode(',', $value);
                $values = array_map('trim', $values);
                $whereMethod = $logic === 'and' && $isFirst ? 'whereIn' :
                             ($logic === 'or' ? 'orWhereIn' : 'whereIn');
                $query->{$whereMethod}($field, $values);
                break;

            case 'not_in':
                $values = is_array($value) ? $value : explode(',', $value);
                $values = array_map('trim', $values);
                $whereMethod = $logic === 'and' && $isFirst ? 'whereNotIn' :
                             ($logic === 'or' ? 'orWhereNotIn' : 'whereNotIn');
                $query->{$whereMethod}($field, $values);
                break;

            case 'between':
                if (is_array($value) && count($value) >= 2) {
                    $whereMethod = $logic === 'and' && $isFirst ? 'whereBetween' :
                                 ($logic === 'or' ? 'orWhereBetween' : 'whereBetween');
                    $query->{$whereMethod}($field, [$value[0], $value[1]]);
                }
                break;

            case 'not_between':
                if (is_array($value) && count($value) >= 2) {
                    $whereMethod = $logic === 'and' && $isFirst ? 'whereNotBetween' :
                                 ($logic === 'or' ? 'orWhereNotBetween' : 'whereNotBetween');
                    $query->{$whereMethod}($field, [$value[0], $value[1]]);
                }
                break;

            case 'date_equals':
                $query->{$whereMethod}(DB::raw("DATE({$field})"), '=', $value);
                break;

            case 'date_greater_than':
                $query->{$whereMethod}(DB::raw("DATE({$field})"), '>', $value);
                break;

            case 'date_less_than':
                $query->{$whereMethod}(DB::raw("DATE({$field})"), '<', $value);
                break;

            case 'days_ago':
                $daysAgo = (int) $value;
                $targetDate = now()->subDays($daysAgo)->format('Y-m-d');
                $query->{$whereMethod}(DB::raw("DATE({$field})"), '=', $targetDate);
                break;

            case 'last_n_days':
                $days = (int) $value;
                $startDate = now()->subDays($days)->format('Y-m-d');
                $query->{$whereMethod}(DB::raw("DATE({$field})"), '>=', $startDate);
                break;

            case 'has_tag':
                $query->{$whereMethod}('tags', 'LIKE', "%\"{$value}\"%");
                break;

            case 'not_has_tag':
                $query->{$whereMethod}('tags', 'NOT LIKE', "%\"{$value}\"%");
                break;

            case 'custom_field_equals':
                if (isset($condition['custom_field'])) {
                    $customField = $condition['custom_field'];
                    $query->{$whereMethod}('custom_fields->'.$customField, '=', $value);
                }
                break;

            case 'custom_field_contains':
                if (isset($condition['custom_field'])) {
                    $customField = $condition['custom_field'];
                    $query->{$whereMethod}('custom_fields->'.$customField, 'LIKE', "%{$value}%");
                }
                break;

            default:
                Log::warning("Unknown operator '{$operator}' in segment condition");
                break;
        }

        return $query;
    }

    /**
     * Get the appropriate where method based on logic and position.
     */
    protected function getWhereMethod(string $logic, bool $isFirst): string
    {
        if ($isFirst) {
            return 'where';
        }

        return $logic === 'and' ? 'where' : 'orWhere';
    }

    /**
     * Handle a job failure.
     */
    public function failed(Exception $exception): void
    {
        Log::error('RefreshDynamicSegmentsJob permanently failed: '.$exception->getMessage());

        if ($this->segment) {
            Log::error("Failed segment ID: {$this->segment->id}");
        }
    }
}
