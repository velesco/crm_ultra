<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LoginAttempt;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SecurityController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:super_admin|admin']);
    }

    /**
     * Display the security dashboard
     */
    public function index(Request $request)
    {
        $stats = $this->getSecurityStats();

        $recentAttempts = LoginAttempt::with('user')
            ->latest()
            ->take(50)
            ->get();

        $suspiciousIps = $this->getSuspiciousIPs();
        $blockedUsers = $this->getBlockedUsers();
        $topFailedEmails = $this->getTopFailedEmails();

        if ($request->ajax()) {
            return response()->json([
                'stats' => $stats,
                'recent_attempts' => $recentAttempts,
                'suspicious_ips' => $suspiciousIps,
                'blocked_users' => $blockedUsers,
            ]);
        }

        return view('admin.security.index', compact(
            'stats',
            'recentAttempts',
            'suspiciousIps',
            'blockedUsers',
            'topFailedEmails'
        ));
    }

    /**
     * Display login attempts
     */
    public function loginAttempts(Request $request)
    {
        $query = LoginAttempt::with('user')->latest();

        // Filters
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('email')) {
            $query->where('email', 'like', '%'.$request->email.'%');
        }

        if ($request->filled('ip_address')) {
            $query->where('ip_address', 'like', '%'.$request->ip_address.'%');
        }

        if ($request->filled('date_from')) {
            $query->where('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('created_at', '<=', $request->date_to.' 23:59:59');
        }

        $attempts = $query->paginate(50);

        if ($request->ajax()) {
            return view('admin.security.partials.attempts-table', compact('attempts'))->render();
        }

        return view('admin.security.login-attempts', compact('attempts'));
    }

    /**
     * Block IP address
     */
    public function blockIp(Request $request)
    {
        $request->validate([
            'ip_address' => 'required|ip',
            'duration' => 'required|integer|min:1|max:8760', // Max 1 year in hours
            'reason' => 'nullable|string|max:500',
        ]);

        $blockedUntil = now()->addHours($request->duration);

        LoginAttempt::recordBlocked(
            'system_block',
            $request->ip_address,
            $blockedUntil,
            [
                'reason' => $request->reason,
                'blocked_by' => auth()->id(),
                'manual_block' => true,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => "IP {$request->ip_address} a fost blocat până la ".$blockedUntil->format('d/m/Y H:i'),
        ]);
    }

    /**
     * Unblock IP address
     */
    public function unblockIp(Request $request)
    {
        $request->validate([
            'ip_address' => 'required|ip',
        ]);

        $updated = LoginAttempt::where('ip_address', $request->ip_address)
            ->where('blocked_until', '>', now())
            ->update(['blocked_until' => now()]);

        return response()->json([
            'success' => true,
            'message' => $updated > 0
                ? "IP {$request->ip_address} a fost deblocat"
                : 'IP-ul nu era blocat',
        ]);
    }

    /**
     * Block user email
     */
    public function blockUser(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'duration' => 'required|integer|min:1|max:8760',
            'reason' => 'nullable|string|max:500',
        ]);

        $blockedUntil = now()->addHours($request->duration);
        $ip = request()->ip();

        LoginAttempt::recordBlocked(
            $request->email,
            $ip,
            $blockedUntil,
            [
                'reason' => $request->reason,
                'blocked_by' => auth()->id(),
                'manual_block' => true,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => "Email {$request->email} a fost blocat până la ".$blockedUntil->format('d/m/Y H:i'),
        ]);
    }

    /**
     * Unblock user email
     */
    public function unblockUser(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $updated = LoginAttempt::where('email', $request->email)
            ->where('blocked_until', '>', now())
            ->update(['blocked_until' => now()]);

        return response()->json([
            'success' => true,
            'message' => $updated > 0
                ? "Email {$request->email} a fost deblocat"
                : 'Email-ul nu era blocat',
        ]);
    }

    /**
     * Get security analytics data for charts
     */
    public function chartData(Request $request)
    {
        $period = $request->get('period', 'week'); // day, week, month

        switch ($period) {
            case 'day':
                $data = $this->getDailyChartData();
                break;
            case 'month':
                $data = $this->getMonthlyChartData();
                break;
            default:
                $data = $this->getWeeklyChartData();
        }

        return response()->json($data);
    }

    /**
     * Clear old login attempts
     */
    public function clearOldAttempts(Request $request)
    {
        $request->validate([
            'days' => 'required|integer|min:1|max:365',
        ]);

        $deleted = LoginAttempt::clearOldAttempts($request->days);

        return response()->json([
            'success' => true,
            'message' => "Au fost șterse {$deleted} înregistrări mai vechi de {$request->days} zile",
            'deleted_count' => $deleted,
        ]);
    }

    /**
     * Export security data
     */
    public function export(Request $request)
    {
        $query = LoginAttempt::with('user')->latest();

        // Apply same filters as index
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('email')) {
            $query->where('email', 'like', '%'.$request->email.'%');
        }
        if ($request->filled('ip_address')) {
            $query->where('ip_address', 'like', '%'.$request->ip_address.'%');
        }
        if ($request->filled('date_from')) {
            $query->where('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('created_at', '<=', $request->date_to.' 23:59:59');
        }

        $attempts = $query->get();

        $filename = 'security_log_'.now()->format('Y_m_d_H_i_s').'.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ];

        $callback = function () use ($attempts) {
            $file = fopen('php://output', 'w');

            // CSV Header
            fputcsv($file, [
                'ID', 'Email', 'IP Address', 'Type', 'User Agent',
                'Location', 'Device', 'Browser', 'User ID', 'Username',
                'Blocked Until', 'Created At',
            ]);

            foreach ($attempts as $attempt) {
                fputcsv($file, [
                    $attempt->id,
                    $attempt->email,
                    $attempt->ip_address,
                    ucfirst($attempt->type),
                    $attempt->user_agent,
                    $attempt->location ?? 'Unknown',
                    $attempt->device ?? 'Unknown',
                    $attempt->browser ?? 'Unknown',
                    $attempt->user_id,
                    $attempt->user?->name ?? 'N/A',
                    $attempt->blocked_until?->format('Y-m-d H:i:s') ?? 'N/A',
                    $attempt->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Get security statistics
     */
    private function getSecurityStats(): array
    {
        return LoginAttempt::getSecurityStats();
    }

    /**
     * Get suspicious IP addresses
     */
    private function getSuspiciousIPs(): array
    {
        return LoginAttempt::select('ip_address')
            ->selectRaw('COUNT(*) as attempt_count')
            ->selectRaw('SUM(CASE WHEN type = "failed" THEN 1 ELSE 0 END) as failed_count')
            ->selectRaw('MAX(created_at) as last_attempt')
            ->recent(24)
            ->groupBy('ip_address')
            ->having('failed_count', '>=', 3)
            ->orderBy('failed_count', 'desc')
            ->limit(10)
            ->get()
            ->toArray();
    }

    /**
     * Get currently blocked users
     */
    private function getBlockedUsers(): array
    {
        return LoginAttempt::currentlyBlocked()
            ->with('user')
            ->orderBy('blocked_until', 'desc')
            ->limit(20)
            ->get()
            ->toArray();
    }

    /**
     * Get top failed email attempts
     */
    private function getTopFailedEmails(): array
    {
        return LoginAttempt::select('email')
            ->selectRaw('COUNT(*) as attempt_count')
            ->selectRaw('MAX(created_at) as last_attempt')
            ->failed()
            ->recent(168) // Last week
            ->groupBy('email')
            ->orderBy('attempt_count', 'desc')
            ->limit(10)
            ->get()
            ->toArray();
    }

    /**
     * Get daily chart data (last 24 hours)
     */
    private function getDailyChartData(): array
    {
        $hours = collect();
        for ($i = 23; $i >= 0; $i--) {
            $hour = now()->subHours($i);
            $hours->push([
                'label' => $hour->format('H:i'),
                'timestamp' => $hour,
            ]);
        }

        $attempts = LoginAttempt::select(
            DB::raw('DATE_FORMAT(created_at, "%Y-%m-%d %H") as hour'),
            DB::raw('type'),
            DB::raw('COUNT(*) as count')
        )
            ->where('created_at', '>=', now()->subHours(24))
            ->groupBy('hour', 'type')
            ->get()
            ->groupBy('hour');

        return $hours->map(function ($hour) use ($attempts) {
            $hourKey = $hour['timestamp']->format('Y-m-d H');
            $hourAttempts = $attempts->get($hourKey, collect());

            return [
                'label' => $hour['label'],
                'failed' => $hourAttempts->where('type', 'failed')->sum('count'),
                'success' => $hourAttempts->where('type', 'success')->sum('count'),
                'blocked' => $hourAttempts->where('type', 'blocked')->sum('count'),
            ];
        })->values()->toArray();
    }

    /**
     * Get weekly chart data (last 7 days)
     */
    private function getWeeklyChartData(): array
    {
        $days = collect();
        for ($i = 6; $i >= 0; $i--) {
            $day = now()->subDays($i);
            $days->push([
                'label' => $day->format('M j'),
                'timestamp' => $day,
            ]);
        }

        $attempts = LoginAttempt::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('type'),
            DB::raw('COUNT(*) as count')
        )
            ->where('created_at', '>=', now()->subDays(7))
            ->groupBy('date', 'type')
            ->get()
            ->groupBy('date');

        return $days->map(function ($day) use ($attempts) {
            $dateKey = $day['timestamp']->format('Y-m-d');
            $dayAttempts = $attempts->get($dateKey, collect());

            return [
                'label' => $day['label'],
                'failed' => $dayAttempts->where('type', 'failed')->sum('count'),
                'success' => $dayAttempts->where('type', 'success')->sum('count'),
                'blocked' => $dayAttempts->where('type', 'blocked')->sum('count'),
            ];
        })->values()->toArray();
    }

    /**
     * Get monthly chart data (last 30 days)
     */
    private function getMonthlyChartData(): array
    {
        $days = collect();
        for ($i = 29; $i >= 0; $i--) {
            $day = now()->subDays($i);
            $days->push([
                'label' => $day->format('M j'),
                'timestamp' => $day,
            ]);
        }

        $attempts = LoginAttempt::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('type'),
            DB::raw('COUNT(*) as count')
        )
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date', 'type')
            ->get()
            ->groupBy('date');

        return $days->map(function ($day) use ($attempts) {
            $dateKey = $day['timestamp']->format('Y-m-d');
            $dayAttempts = $attempts->get($dateKey, collect());

            return [
                'label' => $day['label'],
                'failed' => $dayAttempts->where('type', 'failed')->sum('count'),
                'success' => $dayAttempts->where('type', 'success')->sum('count'),
                'blocked' => $dayAttempts->where('type', 'blocked')->sum('count'),
            ];
        })->values()->toArray();
    }
}
