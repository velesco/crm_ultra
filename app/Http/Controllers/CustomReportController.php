<?php

namespace App\Http\Controllers;

use App\Models\CustomReport;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CustomReportController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:super_admin|admin|manager']);
    }

    /**
     * Display listing of custom reports
     */
    public function index(Request $request)
    {
        $query = CustomReport::with(['creator'])
            ->accessibleBy(Auth::id());

        // Apply filters
        if ($request->filled('category')) {
            $query->byCategory($request->category);
        }

        if ($request->filled('data_source')) {
            $query->byDataSource($request->data_source);
        }

        if ($request->filled('visibility')) {
            $query->where('visibility', $request->visibility);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }

        // Apply sorting
        $sortBy = $request->get('sort_by', 'updated_at');
        $sortDirection = $request->get('sort_direction', 'desc');
        $query->orderBy($sortBy, $sortDirection);

        $reports = $query->paginate(15);

        // Get statistics
        $stats = $this->getReportStatistics();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.custom-reports.table', compact('reports'))->render(),
                'pagination' => $reports->links()->render()
            ]);
        }

        return view('admin.custom-reports.index', compact('reports', 'stats'));
    }

    /**
     * Show form for creating new report
     */
    public function create()
    {
        $categories = CustomReport::getCategories();
        $dataSources = (new CustomReport())->getAvailableDataSources();
        $operators = CustomReport::getFilterOperators();
        $chartTypes = CustomReport::getChartTypes();

        return view('admin.custom-reports.create', compact('categories', 'dataSources', 'operators', 'chartTypes'));
    }

    /**
     * Store new report
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'required|string|in:' . implode(',', array_keys(CustomReport::getCategories())),
            'data_source' => 'required|string',
            'columns' => 'required|array|min:1',
            'columns.*' => 'string',
            'visibility' => 'required|in:private,shared,public',
            'export_format' => 'required|in:table,chart,both',
            'filters' => 'nullable|array',
            'sorting' => 'nullable|array',
            'grouping' => 'nullable|array',
            'chart_config' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            return back()->withErrors($validator)->withInput();
        }

        try {
            $report = CustomReport::create([
                'name' => $request->name,
                'description' => $request->description,
                'category' => $request->category,
                'data_source' => $request->data_source,
                'columns' => $request->columns,
                'filters' => $request->filters,
                'sorting' => $request->sorting,
                'grouping' => $request->grouping,
                'aggregations' => $request->aggregations,
                'chart_config' => $request->chart_config,
                'visibility' => $request->visibility,
                'export_format' => $request->export_format,
                'created_by' => Auth::id(),
                'updated_by' => Auth::id()
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Report created successfully!',
                    'redirect' => route('admin.custom-reports.show', $report)
                ]);
            }

            return redirect()->route('admin.custom-reports.show', $report)
                ->with('success', 'Report created successfully!');

        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json(['error' => 'Failed to create report: ' . $e->getMessage()], 500);
            }
            return back()->withInput()->with('error', 'Failed to create report: ' . $e->getMessage());
        }
    }

    /**
     * Display report details and results
     */
    public function show(CustomReport $customReport, Request $request)
    {
        if (!$customReport->canUserAccess(Auth::id())) {
            abort(403, 'You do not have permission to view this report.');
        }

        $limit = $request->get('limit', 100);
        
        try {
            $reportData = $customReport->executeReport($limit);
            $chartData = $customReport->getChartData();
            
            return view('admin.custom-reports.show', compact('customReport', 'reportData', 'chartData'));
            
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to execute report: ' . $e->getMessage());
        }
    }

    /**
     * Show form for editing report
     */
    public function edit(CustomReport $customReport)
    {
        if (!$customReport->canUserAccess(Auth::id()) && Auth::user()->cannot('update', $customReport)) {
            abort(403, 'You do not have permission to edit this report.');
        }

        $categories = CustomReport::getCategories();
        $dataSources = (new CustomReport())->getAvailableDataSources();
        $operators = CustomReport::getFilterOperators();
        $chartTypes = CustomReport::getChartTypes();

        return view('admin.custom-reports.edit', compact('customReport', 'categories', 'dataSources', 'operators', 'chartTypes'));
    }

    /**
     * Update report
     */
    public function update(Request $request, CustomReport $customReport)
    {
        if (!$customReport->canUserAccess(Auth::id()) && Auth::user()->cannot('update', $customReport)) {
            abort(403, 'You do not have permission to edit this report.');
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'required|string|in:' . implode(',', array_keys(CustomReport::getCategories())),
            'data_source' => 'required|string',
            'columns' => 'required|array|min:1',
            'columns.*' => 'string',
            'visibility' => 'required|in:private,shared,public',
            'export_format' => 'required|in:table,chart,both',
            'filters' => 'nullable|array',
            'sorting' => 'nullable|array',
            'grouping' => 'nullable|array',
            'chart_config' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            return back()->withErrors($validator)->withInput();
        }

        try {
            $customReport->update([
                'name' => $request->name,
                'description' => $request->description,
                'category' => $request->category,
                'data_source' => $request->data_source,
                'columns' => $request->columns,
                'filters' => $request->filters,
                'sorting' => $request->sorting,
                'grouping' => $request->grouping,
                'aggregations' => $request->aggregations,
                'chart_config' => $request->chart_config,
                'visibility' => $request->visibility,
                'export_format' => $request->export_format,
                'updated_by' => Auth::id()
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Report updated successfully!',
                    'redirect' => route('admin.custom-reports.show', $customReport)
                ]);
            }

            return redirect()->route('admin.custom-reports.show', $customReport)
                ->with('success', 'Report updated successfully!');

        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json(['error' => 'Failed to update report: ' . $e->getMessage()], 500);
            }
            return back()->withInput()->with('error', 'Failed to update report: ' . $e->getMessage());
        }
    }

    /**
     * Delete report
     */
    public function destroy(CustomReport $customReport, Request $request)
    {
        if (Auth::user()->cannot('delete', $customReport)) {
            abort(403, 'You do not have permission to delete this report.');
        }

        try {
            $customReport->delete();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Report deleted successfully!'
                ]);
            }

            return redirect()->route('admin.custom-reports.index')
                ->with('success', 'Report deleted successfully!');

        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json(['error' => 'Failed to delete report: ' . $e->getMessage()], 500);
            }
            return back()->with('error', 'Failed to delete report: ' . $e->getMessage());
        }
    }

    /**
     * Duplicate report
     */
    public function duplicate(CustomReport $customReport, Request $request)
    {
        if (!$customReport->canUserAccess(Auth::id())) {
            abort(403, 'You do not have permission to duplicate this report.');
        }

        try {
            $duplicate = $customReport->replicate();
            $duplicate->name = $customReport->name . ' (Copy)';
            $duplicate->visibility = 'private';
            $duplicate->created_by = Auth::id();
            $duplicate->updated_by = Auth::id();
            $duplicate->run_count = 0;
            $duplicate->last_run_at = null;
            $duplicate->save();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Report duplicated successfully!',
                    'redirect' => route('admin.custom-reports.edit', $duplicate)
                ]);
            }

            return redirect()->route('admin.custom-reports.edit', $duplicate)
                ->with('success', 'Report duplicated successfully! You can now customize it.');

        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json(['error' => 'Failed to duplicate report: ' . $e->getMessage()], 500);
            }
            return back()->with('error', 'Failed to duplicate report: ' . $e->getMessage());
        }
    }

    /**
     * Execute report and return JSON data
     */
    public function execute(CustomReport $customReport, Request $request): JsonResponse
    {
        if (!$customReport->canUserAccess(Auth::id())) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        try {
            $limit = $request->get('limit', 100);
            $reportData = $customReport->executeReport($limit);
            
            return response()->json([
                'success' => true,
                'data' => $reportData['data'],
                'metadata' => $reportData['metadata']
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to execute report: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get chart data for report
     */
    public function chartData(CustomReport $customReport): JsonResponse
    {
        if (!$customReport->canUserAccess(Auth::id())) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        try {
            $chartData = $customReport->getChartData();
            
            return response()->json([
                'success' => true,
                'data' => $chartData,
                'config' => $customReport->chart_config
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to generate chart data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export report data
     */
    public function export(CustomReport $customReport, Request $request): StreamedResponse
    {
        if (!$customReport->canUserAccess(Auth::id())) {
            abort(403, 'Access denied');
        }

        $format = $request->get('format', 'csv');
        
        return response()->streamDownload(function () use ($customReport, $format) {
            $reportData = $customReport->executeReport();
            
            if ($format === 'csv') {
                $output = fopen('php://output', 'w');
                
                if (!empty($reportData['data'])) {
                    // Write headers
                    fputcsv($output, array_keys($reportData['data'][0]));
                    
                    // Write data
                    foreach ($reportData['data'] as $row) {
                        fputcsv($output, $row);
                    }
                }
                
                fclose($output);
            }
        }, $customReport->name . '_export_' . date('Y-m-d_H-i-s') . '.' . $format, [
            'Content-Type' => $format === 'csv' ? 'text/csv' : 'application/json',
        ]);
    }

    /**
     * Get available columns for data source
     */
    public function getColumns(Request $request): JsonResponse
    {
        $dataSource = $request->get('data_source');
        
        if (!$dataSource) {
            return response()->json(['error' => 'Data source is required'], 400);
        }

        $report = new CustomReport(['data_source' => $dataSource]);
        $columns = $report->getColumnOptions();
        
        return response()->json([
            'success' => true,
            'columns' => $columns
        ]);
    }

    /**
     * Preview report with current configuration
     */
    public function preview(Request $request): JsonResponse
    {
        try {
            $tempReport = new CustomReport($request->all());
            $reportData = $tempReport->executeReport(10); // Limit to 10 rows for preview
            
            return response()->json([
                'success' => true,
                'data' => $reportData['data'],
                'metadata' => $reportData['metadata']
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Preview failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk actions
     */
    public function bulkAction(Request $request): JsonResponse
    {
        $action = $request->get('action');
        $reportIds = $request->get('report_ids', []);
        
        if (empty($reportIds)) {
            return response()->json(['error' => 'No reports selected'], 400);
        }

        try {
            $reports = CustomReport::whereIn('id', $reportIds)
                ->accessibleBy(Auth::id())
                ->get();

            $count = 0;
            
            switch ($action) {
                case 'delete':
                    foreach ($reports as $report) {
                        if (Auth::user()->can('delete', $report)) {
                            $report->delete();
                            $count++;
                        }
                    }
                    break;
                    
                case 'activate':
                    $reports->each(function ($report) use (&$count) {
                        if (Auth::user()->can('update', $report)) {
                            $report->update(['is_active' => true]);
                            $count++;
                        }
                    });
                    break;
                    
                case 'deactivate':
                    $reports->each(function ($report) use (&$count) {
                        if (Auth::user()->can('update', $report)) {
                            $report->update(['is_active' => false]);
                            $count++;
                        }
                    });
                    break;
                    
                case 'make_private':
                    $reports->each(function ($report) use (&$count) {
                        if (Auth::user()->can('update', $report)) {
                            $report->update(['visibility' => 'private']);
                            $count++;
                        }
                    });
                    break;
                    
                case 'make_shared':
                    $reports->each(function ($report) use (&$count) {
                        if (Auth::user()->can('update', $report)) {
                            $report->update(['visibility' => 'shared']);
                            $count++;
                        }
                    });
                    break;
            }

            return response()->json([
                'success' => true,
                'message' => "Action completed on {$count} report(s)."
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Bulk action failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get report statistics
     */
    private function getReportStatistics(): array
    {
        $userId = Auth::id();
        
        return [
            'total_reports' => CustomReport::accessibleBy($userId)->count(),
            'my_reports' => CustomReport::where('created_by', $userId)->count(),
            'public_reports' => CustomReport::public()->count(),
            'active_reports' => CustomReport::accessibleBy($userId)->active()->count(),
            'categories' => CustomReport::accessibleBy($userId)
                ->select('category', DB::raw('count(*) as count'))
                ->groupBy('category')
                ->pluck('count', 'category')
                ->toArray(),
            'data_sources' => CustomReport::accessibleBy($userId)
                ->select('data_source', DB::raw('count(*) as count'))
                ->groupBy('data_source')
                ->pluck('count', 'data_source')
                ->toArray()
        ];
    }
}
