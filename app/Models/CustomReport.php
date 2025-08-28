<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CustomReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'category',
        'data_source',
        'columns',
        'filters',
        'sorting',
        'grouping',
        'aggregations',
        'chart_config',
        'visibility',
        'is_scheduled',
        'schedule_config',
        'export_format',
        'is_active',
        'last_run_at',
        'run_count',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'columns' => 'array',
        'filters' => 'array',
        'sorting' => 'array',
        'grouping' => 'array',
        'aggregations' => 'array',
        'chart_config' => 'array',
        'schedule_config' => 'array',
        'is_scheduled' => 'boolean',
        'is_active' => 'boolean',
        'last_run_at' => 'datetime',
    ];

    // Relationships
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeByDataSource($query, $dataSource)
    {
        return $query->where('data_source', $dataSource);
    }

    public function scopePublic($query)
    {
        return $query->where('visibility', 'public');
    }

    public function scopeShared($query)
    {
        return $query->whereIn('visibility', ['public', 'shared']);
    }

    public function scopeAccessibleBy($query, $userId)
    {
        return $query->where(function ($q) use ($userId) {
            $q->where('created_by', $userId)
              ->orWhere('visibility', 'public')
              ->orWhere('visibility', 'shared');
        });
    }

    public function scopeScheduled($query)
    {
        return $query->where('is_scheduled', true);
    }

    // Helper Methods
    public function getAvailableDataSources(): array
    {
        return [
            'contacts' => [
                'label' => 'Contacts',
                'table' => 'contacts',
                'columns' => ['id', 'first_name', 'last_name', 'email', 'phone', 'company', 'industry', 'status', 'created_at', 'updated_at']
            ],
            'email_campaigns' => [
                'label' => 'Email Campaigns',
                'table' => 'email_campaigns',
                'columns' => ['id', 'name', 'subject', 'status', 'sent_count', 'opened_count', 'clicked_count', 'created_at']
            ],
            'sms_messages' => [
                'label' => 'SMS Messages',
                'table' => 'sms_messages',
                'columns' => ['id', 'to_number', 'content', 'status', 'sent_at', 'delivered_at', 'cost', 'provider']
            ],
            'whatsapp_messages' => [
                'label' => 'WhatsApp Messages',
                'table' => 'whatsapp_messages',
                'columns' => ['id', 'session_id', 'phone_number', 'content', 'message_type', 'status', 'sent_at']
            ],
            'revenues' => [
                'label' => 'Revenue',
                'table' => 'revenues',
                'columns' => ['id', 'amount', 'currency', 'type', 'source', 'status', 'customer_name', 'created_at']
            ],
            'contact_segments' => [
                'label' => 'Contact Segments',
                'table' => 'contact_segments',
                'columns' => ['id', 'name', 'is_dynamic', 'contact_count', 'created_at']
            ],
            'communications' => [
                'label' => 'Communications',
                'table' => 'communications',
                'columns' => ['id', 'contact_id', 'type', 'status', 'subject', 'sent_at', 'opened_at', 'clicked_at']
            ]
        ];
    }

    public function getColumnOptions(): array
    {
        $dataSources = $this->getAvailableDataSources();
        return $dataSources[$this->data_source]['columns'] ?? [];
    }

    public function executeReport($limit = null): array
    {
        $query = $this->buildQuery();
        
        if ($limit) {
            $query->limit($limit);
        }

        $results = $query->get()->toArray();
        
        // Update run statistics
        $this->increment('run_count');
        $this->update(['last_run_at' => now()]);
        
        return [
            'data' => $results,
            'metadata' => [
                'total_rows' => count($results),
                'columns' => $this->columns,
                'filters_applied' => count($this->filters ?? []),
                'last_run' => $this->last_run_at,
                'run_count' => $this->run_count
            ]
        ];
    }

    public function buildQuery()
    {
        $dataSources = $this->getAvailableDataSources();
        $tableInfo = $dataSources[$this->data_source];
        
        $query = DB::table($tableInfo['table']);
        
        // Apply column selection
        if (!empty($this->columns)) {
            $query->select($this->columns);
        }
        
        // Apply filters
        if (!empty($this->filters)) {
            foreach ($this->filters as $filter) {
                $this->applyFilter($query, $filter);
            }
        }
        
        // Apply sorting
        if (!empty($this->sorting)) {
            foreach ($this->sorting as $sort) {
                $query->orderBy($sort['column'], $sort['direction'] ?? 'asc');
            }
        }
        
        // Apply grouping
        if (!empty($this->grouping)) {
            $query->groupBy($this->grouping);
        }
        
        return $query;
    }

    private function applyFilter($query, $filter)
    {
        $column = $filter['column'];
        $operator = $filter['operator'];
        $value = $filter['value'];
        
        switch ($operator) {
            case 'equals':
                $query->where($column, '=', $value);
                break;
            case 'not_equals':
                $query->where($column, '!=', $value);
                break;
            case 'contains':
                $query->where($column, 'LIKE', "%{$value}%");
                break;
            case 'starts_with':
                $query->where($column, 'LIKE', "{$value}%");
                break;
            case 'ends_with':
                $query->where($column, 'LIKE', "%{$value}");
                break;
            case 'greater_than':
                $query->where($column, '>', $value);
                break;
            case 'less_than':
                $query->where($column, '<', $value);
                break;
            case 'between':
                $query->whereBetween($column, [$value['min'], $value['max']]);
                break;
            case 'in':
                $query->whereIn($column, $value);
                break;
            case 'not_in':
                $query->whereNotIn($column, $value);
                break;
            case 'is_null':
                $query->whereNull($column);
                break;
            case 'is_not_null':
                $query->whereNotNull($column);
                break;
            case 'date_range':
                $query->whereBetween($column, [$value['start'], $value['end']]);
                break;
        }
    }

    public function getChartData(): array
    {
        if (empty($this->chart_config)) {
            return [];
        }
        
        $results = $this->executeReport();
        $chartConfig = $this->chart_config;
        
        // Process data based on chart type
        switch ($chartConfig['type']) {
            case 'line':
            case 'bar':
                return $this->processTimeSeriesData($results['data'], $chartConfig);
            case 'pie':
            case 'doughnut':
                return $this->processPieData($results['data'], $chartConfig);
            default:
                return $results['data'];
        }
    }

    private function processTimeSeriesData($data, $config): array
    {
        $xColumn = $config['x_axis'];
        $yColumn = $config['y_axis'];
        
        $processedData = [];
        foreach ($data as $row) {
            $processedData[] = [
                'x' => $row[$xColumn],
                'y' => $row[$yColumn]
            ];
        }
        
        return $processedData;
    }

    private function processPieData($data, $config): array
    {
        $labelColumn = $config['label_column'];
        $valueColumn = $config['value_column'];
        
        $processedData = [];
        foreach ($data as $row) {
            $processedData[] = [
                'label' => $row[$labelColumn],
                'value' => $row[$valueColumn]
            ];
        }
        
        return $processedData;
    }

    public function canUserAccess($userId): bool
    {
        return $this->created_by == $userId || 
               $this->visibility == 'public' || 
               $this->visibility == 'shared';
    }

    public function getShareUrl(): string
    {
        return route('admin.custom-reports.public', $this->id);
    }

    // Static methods
    public static function getCategories(): array
    {
        return [
            'general' => 'General Reports',
            'contacts' => 'Contact Reports',
            'campaigns' => 'Campaign Reports',
            'revenue' => 'Revenue Reports',
            'system' => 'System Reports'
        ];
    }

    public static function getFilterOperators(): array
    {
        return [
            'equals' => 'Equals',
            'not_equals' => 'Not Equals',
            'contains' => 'Contains',
            'starts_with' => 'Starts With',
            'ends_with' => 'Ends With',
            'greater_than' => 'Greater Than',
            'less_than' => 'Less Than',
            'between' => 'Between',
            'in' => 'In List',
            'not_in' => 'Not In List',
            'is_null' => 'Is Empty',
            'is_not_null' => 'Is Not Empty',
            'date_range' => 'Date Range'
        ];
    }

    public static function getChartTypes(): array
    {
        return [
            'table' => 'Table',
            'line' => 'Line Chart',
            'bar' => 'Bar Chart',
            'pie' => 'Pie Chart',
            'doughnut' => 'Doughnut Chart'
        ];
    }
}
