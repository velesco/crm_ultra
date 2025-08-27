<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Import Completed - {{ $companyName }}</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            line-height: 1.6;
            color: #374151;
            margin: 0;
            padding: 0;
            background-color: #f9fafb;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            color: #ffffff;
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }
        .content {
            padding: 30px;
        }
        .status-success {
            background-color: #d1fae5;
            border: 1px solid #a7f3d0;
            border-radius: 8px;
            padding: 16px;
            margin: 20px 0;
        }
        .status-error {
            background-color: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 8px;
            padding: 16px;
            margin: 20px 0;
        }
        .status-icon {
            font-size: 20px;
            margin-right: 8px;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 16px;
            margin: 24px 0;
        }
        .stat-card {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 16px;
            text-align: center;
        }
        .stat-number {
            font-size: 24px;
            font-weight: 700;
            color: #1f2937;
            margin: 0;
        }
        .stat-label {
            font-size: 12px;
            color: #6b7280;
            text-transform: uppercase;
            margin: 4px 0 0 0;
            font-weight: 500;
        }
        .button {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #ffffff;
            text-decoration: none;
            padding: 12px 24px;
            border-radius: 6px;
            font-weight: 600;
            margin: 20px 0;
            text-align: center;
        }
        .button:hover {
            opacity: 0.9;
        }
        .footer {
            background-color: #f8fafc;
            padding: 20px 30px;
            text-align: center;
            font-size: 14px;
            color: #6b7280;
            border-top: 1px solid #e2e8f0;
        }
        .file-info {
            background-color: #f0f9ff;
            border: 1px solid #bae6fd;
            border-radius: 8px;
            padding: 16px;
            margin: 20px 0;
        }
        @media (max-width: 600px) {
            .container {
                margin: 0;
                box-shadow: none;
            }
            .header, .content, .footer {
                padding: 20px;
            }
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📊 Import Completed</h1>
        </div>

        <div class="content">
            <h2>Hello {{ $userName }}!</h2>
            
            <p>Your <strong>{{ $importType }}</strong> import has been completed.</p>

            <div class="file-info">
                <strong>📁 File:</strong> {{ $fileName }}<br>
                <strong>⏰ Completed:</strong> {{ now()->format('F j, Y \a\t g:i A') }}
            </div>

            @if($hasErrors)
                <div class="status-error">
                    <span class="status-icon">⚠️</span>
                    <strong>Import completed with some errors that need your attention.</strong>
                </div>
            @else
                <div class="status-success">
                    <span class="status-icon">✅</span>
                    <strong>Import completed successfully!</strong>
                </div>
            @endif

            @if(isset($results['total_rows']))
                <h3>📈 Import Statistics</h3>
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-number">{{ number_format($results['total_rows']) }}</div>
                        <div class="stat-label">Total Rows</div>
                    </div>
                    
                    @if(isset($results['created_contacts']))
                    <div class="stat-card">
                        <div class="stat-number">{{ number_format($results['created_contacts']) }}</div>
                        <div class="stat-label">Contacts Created</div>
                    </div>
                    @endif
                    
                    @if(isset($results['updated_contacts']))
                    <div class="stat-card">
                        <div class="stat-number">{{ number_format($results['updated_contacts']) }}</div>
                        <div class="stat-label">Contacts Updated</div>
                    </div>
                    @endif
                    
                    @if(isset($results['failed_rows']) && $results['failed_rows'] > 0)
                    <div class="stat-card">
                        <div class="stat-number" style="color: #ef4444;">{{ number_format($results['failed_rows']) }}</div>
                        <div class="stat-label">Failed Rows</div>
                    </div>
                    @endif
                    
                    @if(isset($results['duplicate_contacts']) && $results['duplicate_contacts'] > 0)
                    <div class="stat-card">
                        <div class="stat-number" style="color: #f59e0b;">{{ number_format($results['duplicate_contacts']) }}</div>
                        <div class="stat-label">Duplicates</div>
                    </div>
                    @endif
                </div>
            @endif

            <div style="text-align: center; margin: 30px 0;">
                <a href="{{ $dashboardUrl }}" class="button">
                    📋 View Import Details
                </a>
            </div>

            <p>You can review the complete import results and any errors in your dashboard.</p>

            @if($hasErrors)
                <p><strong>Note:</strong> Please check the import history for detailed error information and consider re-importing the failed records.</p>
            @endif

            <p>If you have any questions or need assistance, please don't hesitate to contact our support team.</p>
        </div>

        <div class="footer">
            <p>&copy; {{ date('Y') }} {{ $companyName }}. All rights reserved.</p>
            <p>This is an automated message from your CRM system.</p>
        </div>
    </div>
</body>
</html>
