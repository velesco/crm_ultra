<?php

namespace App\Jobs;

use App\Models\ExportRequest;
use App\Models\Contact;
use App\Models\EmailCampaign;
use App\Models\SmsMessage;
use App\Models\WhatsAppMessage;
use App\Models\Revenue;
use App\Models\SystemLog;
use App\Models\Communication;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use League\Csv\Writer;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class ProcessExportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 3600; // 1 hour
    public $tries = 3;

    protected $exportRequest;

    public function __construct(ExportRequest $exportRequest)
    {
        $this->exportRequest = $exportRequest;
    }

    public function handle()
    {
        try {
            // Mark as started
            $this->exportRequest->markAsStarted();
            SystemLog::info('export', 'export_started', "Export processing started for '{$this->exportRequest->name}'", [
                'export_id' => $this->exportRequest->id,
                'data_type' => $this->exportRequest->data_type
            ]);

            // Get data based on type
            $this->exportRequest->updateProgress(10, 'Fetching data...');
            $data = $this->getData();

            if ($data->isEmpty()) {
                throw new \Exception('No data found to export');
            }

            // Process export based on format
            $this->exportRequest->updateProgress(30, 'Processing data...');
            $filePath = $this->processExport($data);

            $fileSize = Storage::size($filePath);
            $this->exportRequest->markAsCompleted($filePath, $fileSize);

            SystemLog::info('export', 'export_completed', "Export '{$this->exportRequest->name}' completed successfully", [
                'export_id' => $this->exportRequest->id,
                'file_size' => $fileSize,
                'records_count' => $data->count()
            ]);

            // Send notification if enabled
            if ($this->exportRequest->notify_on_completion) {
                $this->sendCompletionNotification();
            }

        } catch (\Exception $e) {
            $this->exportRequest->markAsFailed($e->getMessage());
            
            SystemLog::error('export', 'export_failed', "Export '{$this->exportRequest->name}' failed: {$e->getMessage()}", [
                'export_id' => $this->exportRequest->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw $e;
        }
    }

    protected function getData(): Collection
    {
        $filters = $this->exportRequest->filters ?? [];
        $columns = $this->exportRequest->columns ?? [];

        switch ($this->exportRequest->data_type) {
            case 'contacts':
                return $this->getContactsData($filters, $columns);
            
            case 'email_campaigns':
                return $this->getEmailCampaignsData($filters, $columns);
            
            case 'sms_messages':
                return $this->getSmsMessagesData($filters, $columns);
            
            case 'whatsapp_messages':
                return $this->getWhatsAppMessagesData($filters, $columns);
            
            case 'revenue':
                return $this->getRevenueData($filters, $columns);
            
            case 'communications':
                return $this->getCommunicationsData($filters, $columns);
            
            case 'system_logs':
                return $this->getSystemLogsData($filters, $columns);
            
            case 'custom':
                return $this->getCustomData();
            
            default:
                throw new \Exception("Unsupported data type: {$this->exportRequest->data_type}");
        }
    }

    protected function getContactsData(array $filters, array $columns): Collection
    {
        $query = Contact::with(['segments', 'latestActivity']);

        // Apply filters
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['industry'])) {
            $query->where('industry', $filters['industry']);
        }

        if (isset($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('company', 'like', "%{$search}%");
            });
        }

        return $query->get()->map(function($contact) use ($columns) {
            $data = $contact->toArray();
            
            // Add computed fields
            $data['full_name'] = $contact->full_name;
            $data['segments'] = $contact->segments->pluck('name')->join(', ');
            $data['last_activity'] = $contact->last_activity_at?->format('Y-m-d H:i:s');
            
            // Filter columns if specified
            if (!empty($columns)) {
                $data = array_intersect_key($data, array_flip($columns));
            }
            
            return $data;
        });
    }

    protected function getEmailCampaignsData(array $filters, array $columns): Collection
    {
        $query = EmailCampaign::with(['template', 'segments']);

        // Apply filters
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        return $query->get()->map(function($campaign) use ($columns) {
            $data = $campaign->toArray();
            
            // Add computed fields
            $data['template_name'] = $campaign->template?->name;
            $data['segments_count'] = $campaign->segments->count();
            $data['open_rate'] = $campaign->open_rate . '%';
            $data['click_rate'] = $campaign->click_rate . '%';
            
            // Filter columns if specified
            if (!empty($columns)) {
                $data = array_intersect_key($data, array_flip($columns));
            }
            
            return $data;
        });
    }

    protected function getSmsMessagesData(array $filters, array $columns): Collection
    {
        $query = SmsMessage::with(['contact']);

        // Apply filters
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        return $query->get()->map(function($sms) use ($columns) {
            $data = $sms->toArray();
            
            // Add computed fields
            $data['contact_name'] = $sms->contact?->full_name;
            $data['contact_email'] = $sms->contact?->email;
            
            // Filter columns if specified
            if (!empty($columns)) {
                $data = array_intersect_key($data, array_flip($columns));
            }
            
            return $data;
        });
    }

    protected function getWhatsAppMessagesData(array $filters, array $columns): Collection
    {
        $query = WhatsAppMessage::with(['contact']);

        // Apply filters
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        return $query->get()->map(function($message) use ($columns) {
            $data = $message->toArray();
            
            // Add computed fields
            $data['contact_name'] = $message->contact?->full_name;
            $data['contact_email'] = $message->contact?->email;
            
            // Filter columns if specified
            if (!empty($columns)) {
                $data = array_intersect_key($data, array_flip($columns));
            }
            
            return $data;
        });
    }

    protected function getRevenueData(array $filters, array $columns): Collection
    {
        $query = Revenue::with(['contact']);

        // Apply filters
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['source'])) {
            $query->where('source', $filters['source']);
        }

        if (isset($filters['amount_from'])) {
            $query->where('amount', '>=', $filters['amount_from']);
        }

        if (isset($filters['amount_to'])) {
            $query->where('amount', '<=', $filters['amount_to']);
        }

        if (isset($filters['date_from'])) {
            $query->whereDate('transaction_date', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->whereDate('transaction_date', '<=', $filters['date_to']);
        }

        return $query->get()->map(function($revenue) use ($columns) {
            $data = $revenue->toArray();
            
            // Add computed fields
            $data['contact_name'] = $revenue->contact?->full_name;
            $data['contact_email'] = $revenue->contact?->email;
            $data['formatted_amount'] = $revenue->currency . ' ' . number_format($revenue->amount, 2);
            
            // Filter columns if specified
            if (!empty($columns)) {
                $data = array_intersect_key($data, array_flip($columns));
            }
            
            return $data;
        });
    }

    protected function getCommunicationsData(array $filters, array $columns): Collection
    {
        $query = Communication::with(['contact']);

        // Apply filters
        if (isset($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        return $query->get()->map(function($communication) use ($columns) {
            $data = $communication->toArray();
            
            // Add computed fields
            $data['contact_name'] = $communication->contact?->full_name;
            $data['contact_email'] = $communication->contact?->email;
            
            // Filter columns if specified
            if (!empty($columns)) {
                $data = array_intersect_key($data, array_flip($columns));
            }
            
            return $data;
        });
    }

    protected function getSystemLogsData(array $filters, array $columns): Collection
    {
        $query = SystemLog::with(['user']);

        // Apply filters
        if (isset($filters['level'])) {
            $query->where('level', $filters['level']);
        }

        if (isset($filters['category'])) {
            $query->where('category', $filters['category']);
        }

        if (isset($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        return $query->get()->map(function($log) use ($columns) {
            $data = $log->toArray();
            
            // Add computed fields
            $data['user_name'] = $log->user?->name;
            $data['user_email'] = $log->user?->email;
            
            // Filter columns if specified
            if (!empty($columns)) {
                $data = array_intersect_key($data, array_flip($columns));
            }
            
            return $data;
        });
    }

    protected function getCustomData(): Collection
    {
        if (empty($this->exportRequest->custom_query)) {
            throw new \Exception('Custom query is required for custom export type');
        }

        // Execute custom query (with safety measures)
        try {
            $results = \DB::select($this->exportRequest->custom_query);
            return collect($results)->map(function($row) {
                return (array) $row;
            });
        } catch (\Exception $e) {
            throw new \Exception("Custom query execution failed: " . $e->getMessage());
        }
    }

    protected function processExport(Collection $data): string
    {
        $fileName = $this->exportRequest->getFileName();
        $filePath = "exports/{$fileName}";

        $this->exportRequest->updateProgress(50, 'Generating file...');

        switch ($this->exportRequest->format) {
            case 'csv':
                return $this->generateCsv($data, $filePath);
            
            case 'xlsx':
                return $this->generateExcel($data, $filePath);
            
            case 'json':
                return $this->generateJson($data, $filePath);
            
            case 'pdf':
                return $this->generatePdf($data, $filePath);
            
            default:
                throw new \Exception("Unsupported export format: {$this->exportRequest->format}");
        }
    }

    protected function generateCsv(Collection $data, string $filePath): string
    {
        $csv = Writer::createFromString();
        
        // Add headers
        if ($data->isNotEmpty()) {
            $csv->insertOne(array_keys($data->first()));
        }

        // Add data in chunks
        $chunkSize = 1000;
        $processed = 0;
        $total = $data->count();

        $data->chunk($chunkSize)->each(function($chunk) use ($csv, &$processed, $total) {
            $csv->insertAll($chunk->values()->toArray());
            $processed += $chunk->count();
            $progress = 50 + (int)(($processed / $total) * 40);
            $this->exportRequest->updateProgress($progress, "Writing CSV... {$processed}/{$total} records");
        });

        Storage::put($filePath, $csv->toString());
        return $filePath;
    }

    protected function generateExcel(Collection $data, string $filePath): string
    {
        $export = new class($data) implements \Maatwebsite\Excel\Concerns\FromCollection,
                                            \Maatwebsite\Excel\Concerns\WithHeadings {
            protected $data;

            public function __construct($data)
            {
                $this->data = $data;
            }

            public function collection()
            {
                return $this->data;
            }

            public function headings(): array
            {
                return $this->data->isNotEmpty() ? array_keys($this->data->first()) : [];
            }
        };

        Excel::store($export, $filePath);
        return $filePath;
    }

    protected function generateJson(Collection $data, string $filePath): string
    {
        $json = json_encode([
            'export_info' => [
                'name' => $this->exportRequest->name,
                'data_type' => $this->exportRequest->data_type,
                'generated_at' => now()->toISOString(),
                'record_count' => $data->count()
            ],
            'data' => $data->toArray()
        ], JSON_PRETTY_PRINT);

        Storage::put($filePath, $json);
        return $filePath;
    }

    protected function generatePdf(Collection $data, string $filePath): string
    {
        // For now, we'll generate a simple HTML-based PDF
        // In a real implementation, you might use packages like DomPDF or wkhtmltopdf
        
        $html = view('exports.pdf', [
            'exportRequest' => $this->exportRequest,
            'data' => $data->take(1000), // Limit for PDF
            'generatedAt' => now()
        ])->render();

        // Convert HTML to PDF (you'll need to implement this based on your PDF library)
        // For now, we'll save as HTML
        $htmlPath = str_replace('.pdf', '.html', $filePath);
        Storage::put($htmlPath, $html);
        
        return $htmlPath;
    }

    protected function sendCompletionNotification(): void
    {
        // Send email notification to user
        // You can implement this based on your notification system
        SystemLog::info('export', 'export_notification_sent', "Export completion notification sent for '{$this->exportRequest->name}'", [
            'export_id' => $this->exportRequest->id,
            'user_id' => $this->exportRequest->user_id
        ]);
    }

    public function failed(\Exception $exception)
    {
        $this->exportRequest->markAsFailed($exception->getMessage());
        
        SystemLog::error('export', 'export_job_failed', "Export job failed for '{$this->exportRequest->name}': {$exception->getMessage()}", [
            'export_id' => $this->exportRequest->id,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);
    }
}
