@extends('layouts.app')

@section('title', 'Contact Analytics')

@section('styles')
<style>
    .contact-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 15px;
        padding: 2rem;
        color: white;
        margin-bottom: 1.5rem;
        position: relative;
        overflow: hidden;
    }

    .contact-card::before {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        width: 120px;
        height: 120px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 50%;
        transform: translate(40px, -40px);
    }

    .contact-card.acquisition {
        background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
    }

    .contact-card.engagement {
        background: linear-gradient(135deg, #fc466b 0%, #3f5efb 100%);
    }

    .contact-card.quality {
        background: linear-gradient(135deg, #fdbb2d 0%, #22c1c3 100%);
    }

    .lifecycle-stage {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        margin-bottom: 1rem;
        border-left: 4px solid #667eea;
    }

    .lifecycle-stage.lead {
        border-left-color: #fbbf24;
    }

    .lifecycle-stage.prospect {
        border-left-color: #3b82f6;
    }

    .lifecycle-stage.customer {
        border-left-color: #10b981;
    }

    .lifecycle-stage.advocate {
        border-left-color: #8b5cf6;
    }

    .segment-card {
        background: white;
        border-radius: 12px;
        padding: 1.25rem;
        margin-bottom: 1rem;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
    }

    .segment-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .quality-metric {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1rem;
        background: #f8f9fa;
        border-radius: 8px;
        margin-bottom: 0.75rem;
    }

    .quality-score {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: bold;
        font-size: 1.1rem;
    }

    .score-excellent {
        background: linear-gradient(135deg, #10b981, #059669);
    }

    .score-good {
        background: linear-gradient(135deg, #3b82f6, #2563eb);
    }

    .score-fair {
        background: linear-gradient(135deg, #f59e0b, #d97706);
    }

    .score-poor {
        background: linear-gradient(135deg, #ef4444, #dc2626);
    }

    .acquisition-source {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1rem;
        background: white;
        border-radius: 8px;
        margin-bottom: 0.5rem;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }

    .source-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        margin-right: 1rem;
    }

    .chart-container {
        background: white;
        border-radius: 15px;
        padding: 1.5rem;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        margin-bottom: 1.5rem;
    }

    .engagement-timeline {
        position: relative;
        padding: 1rem 0;
    }

    .timeline-item {
        display: flex;
        align-items: center;
        margin-bottom: 1rem;
        position: relative;
    }

    .timeline-dot {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background: #667eea;
        margin-right: 1rem;
        flex-shrink: 0;
    }

    .timeline-content {
        flex: 1;
        background: #f8f9fa;
        padding: 0.75rem 1rem;
        border-radius: 8px;
    }

    .contact-funnel {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 1rem;
    }

    .funnel-stage {
        flex: 1;
        text-align: center;
        padding: 1rem;
        background: #f8f9fa;
        margin: 0 0.25rem;
        border-radius: 8px;
        position: relative;
    }

    .funnel-stage::after {
        content: '→';
        position: absolute;
        right: -15px;
        top: 50%;
        transform: translateY(-50%);
        color: #6b7280;
        font-size: 1.2rem;
    }

    .funnel-stage:last-child::after {
        display: none;
    }

    .funnel-value {
        font-size: 1.5rem;
        font-weight: bold;
        color: #667eea;
        margin-bottom: 0.25rem;
    }

    .funnel-label {
        font-size: 0.875rem;
        color: #6b7280;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.analytics.index') }}">Analytics</a></li>
                    <li class="breadcrumb-item active">Contact Analytics</li>
                </ol>
            </nav>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-users text-primary me-2"></i>
                Contact Analytics
            </h1>
            <p class="text-muted mb-0">Contact lifecycle analysis and engagement insights</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-success" onclick="exportContacts()">
                <i class="fas fa-download me-1"></i> Export Report
            </button>
            <button class="btn btn-primary" onclick="refreshData()">
                <i class="fas fa-sync-alt me-1"></i> Refresh
            </button>
        </div>
    </div>

    <!-- Date Filter -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row align-items-end">
                <div class="col-md-3">
                    <label for="start_date" class="form-label">Start Date</label>
                    <input type="date" class="form-control" id="start_date" value="{{ $startDate }}">
                </div>
                <div class="col-md-3">
                    <label for="end_date" class="form-label">End Date</label>
                    <input type="date" class="form-control" id="end_date" value="{{ $endDate }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Quick Filters</label>
                    <div class="btn-group w-100" role="group">
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="setDateRange('30days')">30D</button>
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="setDateRange('90days')">90D</button>
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="setDateRange('1year')">1Y</button>
                    </div>
                </div>
                <div class="col-md-3">
                    <button class="btn btn-primary w-100" onclick="updateDateRange()">
                        <i class="fas fa-search me-1"></i> Update
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Overview Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="contact-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="h2 mb-0">12,847</div>
                        <div class="small opacity-75">Total Contacts</div>
                        <div class="d-flex align-items-center mt-2">
                            <i class="fas fa-arrow-up trend-up me-1"></i>
                            <span class="trend-up">+423</span>
                            <span class="ms-2 opacity-75">this month</span>
                        </div>
                    </div>
                    <div class="text-white-50">
                        <i class="fas fa-address-book fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="contact-card acquisition">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="h2 mb-0">847</div>
                        <div class="small opacity-75">New Acquisitions</div>
                        <div class="d-flex align-items-center mt-2">
                            <i class="fas fa-arrow-up trend-up me-1"></i>
                            <span class="trend-up">+18.3%</span>
                            <span class="ms-2 opacity-75">vs last period</span>
                        </div>
                    </div>
                    <div class="text-white-50">
                        <i class="fas fa-user-plus fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="contact-card engagement">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="h2 mb-0">76.4%</div>
                        <div class="small opacity-75">Engagement Rate</div>
                        <div class="d-flex align-items-center mt-2">
                            <i class="fas fa-arrow-up trend-up me-1"></i>
                            <span class="trend-up">+5.7%</span>
                            <span class="ms-2 opacity-75">improvement</span>
                        </div>
                    </div>
                    <div class="text-white-50">
                        <i class="fas fa-heartbeat fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="contact-card quality">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="h2 mb-0">8.7</div>
                        <div class="small opacity-75">Quality Score</div>
                        <div class="d-flex align-items-center mt-2">
                            <i class="fas fa-arrow-up trend-up me-1"></i>
                            <span class="trend-up">+0.3</span>
                            <span class="ms-2 opacity-75">improvement</span>
                        </div>
                    </div>
                    <div class="text-white-50">
                        <i class="fas fa-star fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Contact Acquisition & Lifecycle -->
    <div class="row mb-4">
        <div class="col-lg-8">
            <div class="chart-container">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">Contact Acquisition Trends</h5>
                    <div class="btn-group btn-group-sm" role="group">
                        <button type="button" class="btn btn-outline-primary active" onclick="updateAcquisitionChart('daily')">Daily</button>
                        <button type="button" class="btn btn-outline-primary" onclick="updateAcquisitionChart('weekly')">Weekly</button>
                        <button type="button" class="btn btn-outline-primary" onclick="updateAcquisitionChart('monthly')">Monthly</button>
                    </div>
                </div>
                <canvas id="acquisitionChart" height="400"></canvas>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="chart-container">
                <h5 class="mb-3">Acquisition Sources</h5>
                <div class="acquisition-source">
                    <div class="d-flex align-items-center">
                        <div class="source-icon" style="background: #667eea;">
                            <i class="fas fa-globe"></i>
                        </div>
                        <div>
                            <div class="fw-bold">Website</div>
                            <div class="small text-muted">5,247 contacts</div>
                        </div>
                    </div>
                    <div class="text-end">
                        <div class="fw-bold">40.8%</div>
                        <div class="small text-success">+12.3%</div>
                    </div>
                </div>
                
                <div class="acquisition-source">
                    <div class="d-flex align-items-center">
                        <div class="source-icon" style="background: #10b981;">
                            <i class="fab fa-facebook"></i>
                        </div>
                        <div>
                            <div class="fw-bold">Social Media</div>
                            <div class="small text-muted">3,124 contacts</div>
                        </div>
                    </div>
                    <div class="text-end">
                        <div class="fw-bold">24.3%</div>
                        <div class="small text-success">+8.7%</div>
                    </div>
                </div>
                
                <div class="acquisition-source">
                    <div class="d-flex align-items-center">
                        <div class="source-icon" style="background: #f59e0b;">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div>
                            <div class="fw-bold">Email Campaigns</div>
                            <div class="small text-muted">2,891 contacts</div>
                        </div>
                    </div>
                    <div class="text-end">
                        <div class="fw-bold">22.5%</div>
                        <div class="small text-success">+15.2%</div>
                    </div>
                </div>
                
                <div class="acquisition-source">
                    <div class="d-flex align-items-center">
                        <div class="source-icon" style="background: #8b5cf6;">
                            <i class="fas fa-handshake"></i>
                        </div>
                        <div>
                            <div class="fw-bold">Referrals</div>
                            <div class="small text-muted">1,585 contacts</div>
                        </div>
                    </div>
                    <div class="text-end">
                        <div class="fw-bold">12.3%</div>
                        <div class="small text-success">+22.1%</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Contact Lifecycle Analysis -->
    <div class="row mb-4">
        <div class="col-lg-6">
            <div class="chart-container">
                <h5 class="mb-3">Contact Lifecycle Stages</h5>
                <canvas id="lifecycleChart" height="300"></canvas>
                
                <div class="mt-3">
                    <div class="contact-funnel">
                        <div class="funnel-stage">
                            <div class="funnel-value">12,847</div>
                            <div class="funnel-label">Total Contacts</div>
                        </div>
                        <div class="funnel-stage">
                            <div class="funnel-value">8,245</div>
                            <div class="funnel-label">Engaged</div>
                        </div>
                        <div class="funnel-stage">
                            <div class="funnel-value">4,187</div>
                            <div class="funnel-label">Qualified</div>
                        </div>
                        <div class="funnel-stage">
                            <div class="funnel-value">1,829</div>
                            <div class="funnel-label">Customers</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-6">
            <div class="chart-container">
                <h5 class="mb-3">Lifecycle Stage Distribution</h5>
                
                <div class="lifecycle-stage lead">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fw-bold">Leads</div>
                            <div class="small text-muted">New contacts, minimal engagement</div>
                        </div>
                        <div class="text-end">
                            <div class="h5 mb-0">4,602</div>
                            <div class="small text-muted">35.8%</div>
                        </div>
                    </div>
                    <div class="progress mt-2" style="height: 6px;">
                        <div class="progress-bar bg-warning" style="width: 35.8%"></div>
                    </div>
                </div>
                
                <div class="lifecycle-stage prospect">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fw-bold">Prospects</div>
                            <div class="small text-muted">Engaged and qualified leads</div>
                        </div>
                        <div class="text-end">
                            <div class="h5 mb-0">3,643</div>
                            <div class="small text-muted">28.4%</div>
                        </div>
                    </div>
                    <div class="progress mt-2" style="height: 6px;">
                        <div class="progress-bar bg-primary" style="width: 28.4%"></div>
                    </div>
                </div>
                
                <div class="lifecycle-stage customer">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fw-bold">Customers</div>
                            <div class="small text-muted">Active paying customers</div>
                        </div>
                        <div class="text-end">
                            <div class="h5 mb-0">3,829</div>
                            <div class="small text-muted">29.8%</div>
                        </div>
                    </div>
                    <div class="progress mt-2" style="height: 6px;">
                        <div class="progress-bar bg-success" style="width: 29.8%"></div>
                    </div>
                </div>
                
                <div class="lifecycle-stage advocate">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fw-bold">Advocates</div>
                            <div class="small text-muted">Loyal customers and promoters</div>
                        </div>
                        <div class="text-end">
                            <div class="h5 mb-0">773</div>
                            <div class="small text-muted">6.0%</div>
                        </div>
                    </div>
                    <div class="progress mt-2" style="height: 6px;">
                        <div class="progress-bar" style="width: 6.0%; background: #8b5cf6;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Engagement & Quality Analysis -->
    <div class="row mb-4">
        <div class="col-lg-6">
            <div class="chart-container">
                <h5 class="mb-3">Contact Engagement Over Time</h5>
                <canvas id="engagementChart" height="300"></canvas>
                
                <div class="engagement-timeline mt-3">
                    <div class="timeline-item">
                        <div class="timeline-dot" style="background: #22c55e;"></div>
                        <div class="timeline-content">
                            <div class="small text-muted">Last 7 days</div>
                            <div class="fw-bold">2,847 active contacts</div>
                            <div class="small">78.2% engagement rate</div>
                        </div>
                    </div>
                    
                    <div class="timeline-item">
                        <div class="timeline-dot" style="background: #3b82f6;"></div>
                        <div class="timeline-content">
                            <div class="small text-muted">Last 30 days</div>
                            <div class="fw-bold">6,521 active contacts</div>
                            <div class="small">65.4% engagement rate</div>
                        </div>
                    </div>
                    
                    <div class="timeline-item">
                        <div class="timeline-dot" style="background: #f59e0b;"></div>
                        <div class="timeline-content">
                            <div class="small text-muted">Last 90 days</div>
                            <div class="fw-bold">9,834 active contacts</div>
                            <div class="small">58.7% engagement rate</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-6">
            <div class="chart-container">
                <h5 class="mb-3">Contact Quality Metrics</h5>
                
                <div class="quality-metric">
                    <div>
                        <div class="fw-bold">Data Completeness</div>
                        <div class="small text-muted">Complete profile information</div>
                    </div>
                    <div class="quality-score score-excellent">94%</div>
                </div>
                
                <div class="quality-metric">
                    <div>
                        <div class="fw-bold">Email Deliverability</div>
                        <div class="small text-muted">Valid and deliverable emails</div>
                    </div>
                    <div class="quality-score score-excellent">97%</div>
                </div>
                
                <div class="quality-metric">
                    <div>
                        <div class="fw-bold">Engagement Score</div>
                        <div class="small text-muted">Recent activity and interactions</div>
                    </div>
                    <div class="quality-score score-good">82%</div>
                </div>
                
                <div class="quality-metric">
                    <div>
                        <div class="fw-bold">Contact Freshness</div>
                        <div class="small text-muted">Recently updated information</div>
                    </div>
                    <div class="quality-score score-fair">71%</div>
                </div>
                
                <div class="quality-metric">
                    <div>
                        <div class="fw-bold">Segmentation Accuracy</div>
                        <div class="small text-muted">Proper segment assignment</div>
                    </div>
                    <div class="quality-score score-excellent">89%</div>
                </div>
                
                <div class="mt-3 p-3 bg-light rounded">
                    <div class="text-center">
                        <div class="h4 text-success mb-1">Overall Quality Score</div>
                        <div class="h2 text-primary">8.7/10</div>
                        <div class="small text-muted">Excellent contact quality</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Contact Segmentation Analysis -->
    <div class="row">
        <div class="col-12">
            <div class="chart-container">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">Contact Segmentation Performance</h5>
                    <div class="btn-group btn-group-sm" role="group">
                        <button type="button" class="btn btn-outline-secondary active" onclick="updateSegmentView('performance')">Performance</button>
                        <button type="button" class="btn btn-outline-secondary" onclick="updateSegmentView('growth')">Growth</button>
                        <button type="button" class="btn btn-outline-secondary" onclick="updateSegmentView('quality')">Quality</button>
                    </div>
                </div>
                
                <div class="row" id="segmentation-content">
                    <div class="col-lg-4 mb-3">
                        <div class="segment-card">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="mb-0">VIP Customers</h6>
                                <span class="badge bg-success">High Value</span>
                            </div>
                            <div class="small text-muted mb-2">Premium customer segment with high engagement</div>
                            
                            <div class="row">
                                <div class="col-4 text-center">
                                    <div class="fw-bold text-primary">1,247</div>
                                    <div class="small text-muted">Contacts</div>
                                </div>
                                <div class="col-4 text-center">
                                    <div class="fw-bold text-success">89.2%</div>
                                    <div class="small text-muted">Engagement</div>
                                </div>
                                <div class="col-4 text-center">
                                    <div class="fw-bold text-info">$4,850</div>
                                    <div class="small text-muted">Avg Value</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-4 mb-3">
                        <div class="segment-card">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="mb-0">Tech Enthusiasts</h6>
                                <span class="badge bg-primary">Growing</span>
                            </div>
                            <div class="small text-muted mb-2">Technology-focused contacts with high activity</div>
                            
                            <div class="row">
                                <div class="col-4 text-center">
                                    <div class="fw-bold text-primary">2,891</div>
                                    <div class="small text-muted">Contacts</div>
                                </div>
                                <div class="col-4 text-center">
                                    <div class="fw-bold text-success">72.8%</div>
                                    <div class="small text-muted">Engagement</div>
                                </div>
                                <div class="col-4 text-center">
                                    <div class="fw-bold text-info">$2,340</div>
                                    <div class="small text-muted">Avg Value</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-4 mb-3">
                        <div class="segment-card">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="mb-0">SMB Prospects</h6>
                                <span class="badge bg-warning">Potential</span>
                            </div>
                            <div class="small text-muted mb-2">Small and medium business prospects</div>
                            
                            <div class="row">
                                <div class="col-4 text-center">
                                    <div class="fw-bold text-primary">3,456</div>
                                    <div class="small text-muted">Contacts</div>
                                </div>
                                <div class="col-4 text-center">
                                    <div class="fw-bold text-success">58.4%</div>
                                    <div class="small text-muted">Engagement</div>
                                </div>
                                <div class="col-4 text-center">
                                    <div class="fw-bold text-info">$1,680</div>
                                    <div class="small text-muted">Avg Value</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-4 mb-3">
                        <div class="segment-card">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="mb-0">Newsletter Subscribers</h6>
                                <span class="badge bg-info">Engaged</span>
                            </div>
                            <div class="small text-muted mb-2">Regular newsletter and content consumers</div>
                            
                            <div class="row">
                                <div class="col-4 text-center">
                                    <div class="fw-bold text-primary">4,829</div>
                                    <div class="small text-muted">Contacts</div>
                                </div>
                                <div class="col-4 text-center">
                                    <div class="fw-bold text-success">45.7%</div>
                                    <div class="small text-muted">Engagement</div>
                                </div>
                                <div class="col-4 text-center">
                                    <div class="fw-bold text-info">$890</div>
                                    <div class="small text-muted">Avg Value</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-4 mb-3">
                        <div class="segment-card">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="mb-0">Inactive Contacts</h6>
                                <span class="badge bg-danger">At Risk</span>
                            </div>
                            <div class="small text-muted mb-2">Contacts with declining engagement</div>
                            
                            <div class="row">
                                <div class="col-4 text-center">
                                    <div class="fw-bold text-primary">987</div>
                                    <div class="small text-muted">Contacts</div>
                                </div>
                                <div class="col-4 text-center">
                                    <div class="fw-bold text-success">12.3%</div>
                                    <div class="small text-muted">Engagement</div>
                                </div>
                                <div class="col-4 text-center">
                                    <div class="fw-bold text-info">$340</div>
                                    <div class="small text-muted">Avg Value</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-4 mb-3">
                        <div class="segment-card">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="mb-0">Recent Signups</h6>
                                <span class="badge bg-secondary">New</span>
                            </div>
                            <div class="small text-muted mb-2">New contacts in the onboarding process</div>
                            
                            <div class="row">
                                <div class="col-4 text-center">
                                    <div class="fw-bold text-primary">437</div>
                                    <div class="small text-muted">Contacts</div>
                                </div>
                                <div class="col-4 text-center">
                                    <div class="fw-bold text-success">68.9%</div>
                                    <div class="small text-muted">Engagement</div>
                                </div>
                                <div class="col-4 text-center">
                                    <div class="fw-bold text-info">$0</div>
                                    <div class="small text-muted">Avg Value</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    let acquisitionChart, lifecycleChart, engagementChart;
    
    document.addEventListener('DOMContentLoaded', function() {
        initializeCharts();
    });

    function initializeCharts() {
        // Acquisition Chart
        const acquisitionCtx = document.getElementById('acquisitionChart').getContext('2d');
        acquisitionChart = new Chart(acquisitionCtx, {
            type: 'line',
            data: {
                labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4', 'Week 5', 'Week 6', 'Week 7', 'Week 8'],
                datasets: [{
                    label: 'New Contacts',
                    data: [89, 124, 156, 198, 134, 167, 189, 203],
                    borderColor: '#667eea',
                    backgroundColor: 'rgba(102, 126, 234, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4
                }, {
                    label: 'Target',
                    data: [150, 150, 150, 150, 150, 150, 150, 150],
                    borderColor: '#94a3b8',
                    borderDash: [5, 5],
                    borderWidth: 2,
                    fill: false
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Lifecycle Chart
        const lifecycleCtx = document.getElementById('lifecycleChart').getContext('2d');
        lifecycleChart = new Chart(lifecycleCtx, {
            type: 'doughnut',
            data: {
                labels: ['Leads', 'Prospects', 'Customers', 'Advocates'],
                datasets: [{
                    data: [4602, 3643, 3829, 773],
                    backgroundColor: ['#fbbf24', '#3b82f6', '#10b981', '#8b5cf6'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // Engagement Chart
        const engagementCtx = document.getElementById('engagementChart').getContext('2d');
        engagementChart = new Chart(engagementCtx, {
            type: 'bar',
            data: {
                labels: ['Last 7 days', 'Last 30 days', 'Last 90 days', 'Last 6 months', 'Last year'],
                datasets: [{
                    label: 'Active Contacts',
                    data: [2847, 6521, 9834, 11247, 12489],
                    backgroundColor: ['#22c55e', '#3b82f6', '#f59e0b', '#ef4444', '#8b5cf6'],
                    borderRadius: 4,
                    borderSkipped: false,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }

    function updateDateRange() {
        const startDate = document.getElementById('start_date').value;
        const endDate = document.getElementById('end_date').value;
        
        if (startDate && endDate) {
            window.location.href = `{{ route('admin.analytics.contacts') }}?start_date=${startDate}&end_date=${endDate}`;
        }
    }

    function setDateRange(period) {
        window.location.href = `{{ route('admin.analytics.contacts') }}?period=${period}`;
    }

    function updateAcquisitionChart(type) {
        // Update button states
        document.querySelectorAll('.btn-group .btn').forEach(btn => btn.classList.remove('active'));
        event.target.classList.add('active');
        
        const sampleData = {
            daily: [12, 18, 23, 15, 28, 34, 19, 25],
            weekly: [89, 124, 156, 198, 134, 167, 189, 203],
            monthly: [387, 456, 523, 612, 578, 689, 734, 812]
        };
        
        acquisitionChart.data.datasets[0].data = sampleData[type];
        acquisitionChart.data.datasets[0].label = `New Contacts (${type})`;
        acquisitionChart.update();
    }

    function updateSegmentView(view) {
        // Update button states
        document.querySelectorAll('.btn-group .btn').forEach(btn => btn.classList.remove('active'));
        event.target.classList.add('active');
        
        console.log('Updating segment view:', view);
        // Here you would typically update the segment cards based on the view type
    }

    function exportContacts() {
        const startDate = document.getElementById('start_date').value;
        const endDate = document.getElementById('end_date').value;
        
        const url = `{{ route('admin.analytics.export') }}?type=contacts&start_date=${startDate}&end_date=${endDate}&format=csv`;
        window.open(url, '_blank');
    }

    function refreshData() {
        window.location.reload();
    }
</script>
@endsection