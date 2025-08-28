<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UserManagementController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'verified']);
        $this->middleware('permission:manage_users')->except(['index', 'show']);
        $this->middleware('permission:view_users')->only(['index', 'show']);
    }

    /**
     * Display a listing of users with advanced filtering and statistics
     */
    public function index(Request $request)
    {
        try {
            $query = User::with(['roles', 'permissions'])
                ->withCount(['emailCampaigns', 'contactsCreated', 'contactSegments']);

            // Advanced filtering
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            }

            if ($request->filled('role')) {
                $query->role($request->role);
            }

            if ($request->filled('status')) {
                if ($request->status === 'active') {
                    $query->whereNotNull('email_verified_at')
                        ->where('is_active', true);
                } elseif ($request->status === 'inactive') {
                    $query->where('is_active', false);
                } elseif ($request->status === 'pending') {
                    $query->whereNull('email_verified_at');
                }
            }

            if ($request->filled('date_from')) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }

            if ($request->filled('date_to')) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }

            // Sorting
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');

            $allowedSorts = ['name', 'email', 'created_at', 'last_login_at', 'email_campaigns_count'];
            if (in_array($sortBy, $allowedSorts)) {
                $query->orderBy($sortBy, $sortOrder);
            }

            $users = $query->paginate(20)->withQueryString();

            // Statistics
            $stats = $this->getUserStats();

            // Roles for filter dropdown
            $roles = Role::all();

            return view('admin.user-management.index', compact('users', 'stats', 'roles'));

        } catch (\Exception $e) {
            Log::error('User Management Index Error: '.$e->getMessage());

            return back()->with('error', 'Error loading users: '.$e->getMessage());
        }
    }

    /**
     * Show the form for creating a new user
     */
    public function create()
    {
        $roles = Role::all();
        $permissions = Permission::all()->groupBy('category');

        return view('admin.user-management.create', compact('roles', 'permissions'));
    }

    /**
     * Store a newly created user
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['exists:roles,name'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['exists:permissions,name'],
            'is_active' => ['boolean'],
            'email_verified' => ['boolean'],
            'send_welcome_email' => ['boolean'],
            'department' => ['nullable', 'string', 'max:100'],
            'position' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            DB::beginTransaction();

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'password' => Hash::make($validated['password']),
                'is_active' => $validated['is_active'] ?? true,
                'email_verified_at' => ($validated['email_verified'] ?? false) ? now() : null,
                'department' => $validated['department'] ?? null,
                'position' => $validated['position'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'created_by' => auth()->id(),
            ]);

            // Assign roles
            if (! empty($validated['roles'])) {
                $user->assignRole($validated['roles']);
            }

            // Assign direct permissions
            if (! empty($validated['permissions'])) {
                $user->givePermissionTo($validated['permissions']);
            }

            DB::commit();

            // Send welcome email if requested
            if ($validated['send_welcome_email'] ?? false) {
                // TODO: Implement welcome email job
                Log::info("Welcome email scheduled for user: {$user->email}");
            }

            Log::info('User created successfully', [
                'user_id' => $user->id,
                'email' => $user->email,
                'created_by' => auth()->id(),
            ]);

            return redirect()
                ->route('admin.user-management.show', $user)
                ->with('success', 'User created successfully!');

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('User creation failed: '.$e->getMessage());

            return back()
                ->withInput()
                ->with('error', 'Failed to create user: '.$e->getMessage());
        }
    }

    /**
     * Display the specified user
     */
    public function show(User $user)
    {
        $user->load(['roles', 'permissions', 'emailCampaigns', 'contactsCreated', 'contactSegments']);

        // User activity statistics
        $stats = [
            'email_campaigns' => $user->emailCampaigns()->count(),
            'contacts_created' => $user->contactsCreated()->count(),
            'segments_created' => $user->contactSegments()->count(),
            'last_login' => $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Never',
            'account_age' => $user->created_at->diffForHumans(),
            'total_logins' => $user->login_count ?? 0,
        ];

        // Recent activity
        $recentActivity = collect();

        // Add email campaigns
        $user->emailCampaigns()->latest()->take(5)->get()->each(function ($campaign) use ($recentActivity) {
            $recentActivity->push([
                'type' => 'email_campaign',
                'title' => "Created email campaign: {$campaign->name}",
                'date' => $campaign->created_at,
                'icon' => 'fas fa-envelope',
                'color' => 'text-primary',
            ]);
        });

        // Add contacts created
        $user->contactsCreated()->latest()->take(5)->get()->each(function ($contact) use ($recentActivity) {
            $recentActivity->push([
                'type' => 'contact',
                'title' => "Created contact: {$contact->first_name} {$contact->last_name}",
                'date' => $contact->created_at,
                'icon' => 'fas fa-user-plus',
                'color' => 'text-success',
            ]);
        });

        $recentActivity = $recentActivity->sortByDesc('date')->take(10);

        return view('admin.user-management.show', compact('user', 'stats', 'recentActivity'));
    }

    /**
     * Show the form for editing the specified user
     */
    public function edit(User $user)
    {
        $roles = Role::all();
        $permissions = Permission::all()->groupBy('category');
        $userRoles = $user->roles->pluck('name')->toArray();
        $userPermissions = $user->permissions->pluck('name')->toArray();

        return view('admin.user-management.edit', compact('user', 'roles', 'permissions', 'userRoles', 'userPermissions'));
    }

    /**
     * Update the specified user
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['exists:roles,name'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['exists:permissions,name'],
            'is_active' => ['boolean'],
            'email_verified' => ['boolean'],
            'department' => ['nullable', 'string', 'max:100'],
            'position' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            DB::beginTransaction();

            $updateData = [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'is_active' => $validated['is_active'] ?? $user->is_active,
                'department' => $validated['department'] ?? null,
                'position' => $validated['position'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'updated_by' => auth()->id(),
            ];

            // Update password if provided
            if (! empty($validated['password'])) {
                $updateData['password'] = Hash::make($validated['password']);
            }

            // Update email verification status
            if (isset($validated['email_verified'])) {
                $updateData['email_verified_at'] = $validated['email_verified'] ? now() : null;
            }

            $user->update($updateData);

            // Sync roles
            if (isset($validated['roles'])) {
                $user->syncRoles($validated['roles']);
            }

            // Sync permissions
            if (isset($validated['permissions'])) {
                $user->syncPermissions($validated['permissions']);
            }

            DB::commit();

            Log::info('User updated successfully', [
                'user_id' => $user->id,
                'email' => $user->email,
                'updated_by' => auth()->id(),
            ]);

            return redirect()
                ->route('admin.user-management.show', $user)
                ->with('success', 'User updated successfully!');

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('User update failed: '.$e->getMessage());

            return back()
                ->withInput()
                ->with('error', 'Failed to update user: '.$e->getMessage());
        }
    }

    /**
     * Remove the specified user from storage
     */
    public function destroy(User $user)
    {
        try {
            // Prevent self-deletion
            if ($user->id === auth()->id()) {
                return back()->with('error', 'You cannot delete your own account.');
            }

            // Prevent deletion of super admin
            if ($user->hasRole('super_admin')) {
                return back()->with('error', 'Super admin account cannot be deleted.');
            }

            DB::beginTransaction();

            // Log the deletion
            Log::info('User deletion initiated', [
                'user_id' => $user->id,
                'email' => $user->email,
                'deleted_by' => auth()->id(),
            ]);

            // Remove roles and permissions
            $user->syncRoles([]);
            $user->syncPermissions([]);

            // Soft delete the user
            $user->delete();

            DB::commit();

            return redirect()
                ->route('admin.user-management.index')
                ->with('success', 'User deleted successfully!');

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('User deletion failed: '.$e->getMessage());

            return back()->with('error', 'Failed to delete user: '.$e->getMessage());
        }
    }

    /**
     * Bulk actions for multiple users
     */
    public function bulkAction(Request $request)
    {
        $validated = $request->validate([
            'action' => ['required', 'in:activate,deactivate,delete,assign_role,remove_role'],
            'user_ids' => ['required', 'array'],
            'user_ids.*' => ['exists:users,id'],
            'role' => ['required_if:action,assign_role,remove_role', 'exists:roles,name'],
        ]);

        try {
            DB::beginTransaction();

            $users = User::whereIn('id', $validated['user_ids'])->get();
            $successCount = 0;

            foreach ($users as $user) {
                // Skip self and super admin for certain actions
                if ($user->id === auth()->id() ||
                    ($user->hasRole('super_admin') && in_array($validated['action'], ['deactivate', 'delete']))) {
                    continue;
                }

                switch ($validated['action']) {
                    case 'activate':
                        $user->update(['is_active' => true]);
                        $successCount++;
                        break;

                    case 'deactivate':
                        $user->update(['is_active' => false]);
                        $successCount++;
                        break;

                    case 'delete':
                        $user->syncRoles([]);
                        $user->syncPermissions([]);
                        $user->delete();
                        $successCount++;
                        break;

                    case 'assign_role':
                        $user->assignRole($validated['role']);
                        $successCount++;
                        break;

                    case 'remove_role':
                        $user->removeRole($validated['role']);
                        $successCount++;
                        break;
                }
            }

            DB::commit();

            Log::info('Bulk user action completed', [
                'action' => $validated['action'],
                'affected_users' => $successCount,
                'performed_by' => auth()->id(),
            ]);

            return back()->with('success', "Bulk action completed successfully! {$successCount} users affected.");

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Bulk user action failed: '.$e->getMessage());

            return back()->with('error', 'Bulk action failed: '.$e->getMessage());
        }
    }

    /**
     * Toggle user active status
     */
    public function toggleStatus(User $user)
    {
        try {
            // Prevent deactivating self
            if ($user->id === auth()->id()) {
                return response()->json(['error' => 'You cannot deactivate your own account.'], 403);
            }

            // Prevent deactivating super admin
            if ($user->hasRole('super_admin')) {
                return response()->json(['error' => 'Super admin account cannot be deactivated.'], 403);
            }

            $user->update(['is_active' => ! $user->is_active]);

            Log::info('User status toggled', [
                'user_id' => $user->id,
                'new_status' => $user->is_active ? 'active' : 'inactive',
                'toggled_by' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'status' => $user->is_active,
                'message' => 'User status updated successfully!',
            ]);

        } catch (\Exception $e) {
            Log::error('Toggle user status failed: '.$e->getMessage());

            return response()->json(['error' => 'Failed to update user status.'], 500);
        }
    }

    /**
     * Get user statistics
     */
    private function getUserStats()
    {
        return [
            'total_users' => User::count(),
            'active_users' => User::where('is_active', true)->count(),
            'inactive_users' => User::where('is_active', false)->count(),
            'pending_verification' => User::whereNull('email_verified_at')->count(),
            'new_this_month' => User::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
            'roles_distribution' => Role::withCount('users')->get(),
            'recent_registrations' => User::latest()->take(5)->get(),
        ];
    }

    /**
     * Export users data
     */
    public function export(Request $request)
    {
        try {
            $query = User::with(['roles', 'permissions']);

            // Apply same filters as index
            if ($request->filled('role')) {
                $query->role($request->role);
            }

            if ($request->filled('status')) {
                if ($request->status === 'active') {
                    $query->where('is_active', true);
                } elseif ($request->status === 'inactive') {
                    $query->where('is_active', false);
                } elseif ($request->status === 'pending') {
                    $query->whereNull('email_verified_at');
                }
            }

            $users = $query->get();

            $csvData = [];
            $csvData[] = [
                'ID', 'Name', 'Email', 'Phone', 'Roles', 'Status',
                'Email Verified', 'Department', 'Position', 'Created At', 'Last Login',
            ];

            foreach ($users as $user) {
                $csvData[] = [
                    $user->id,
                    $user->name,
                    $user->email,
                    $user->phone ?? 'N/A',
                    $user->roles->pluck('name')->join(', ') ?: 'None',
                    $user->is_active ? 'Active' : 'Inactive',
                    $user->email_verified_at ? 'Yes' : 'No',
                    $user->department ?? 'N/A',
                    $user->position ?? 'N/A',
                    $user->created_at->format('Y-m-d H:i:s'),
                    $user->last_login_at ? $user->last_login_at->format('Y-m-d H:i:s') : 'Never',
                ];
            }

            $filename = 'users_export_'.now()->format('Y-m-d_H-i-s').'.csv';

            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ];

            $callback = function () use ($csvData) {
                $file = fopen('php://output', 'w');
                foreach ($csvData as $row) {
                    fputcsv($file, $row);
                }
                fclose($file);
            };

            return response()->stream($callback, 200, $headers);

        } catch (\Exception $e) {
            Log::error('User export failed: '.$e->getMessage());

            return back()->with('error', 'Failed to export users: '.$e->getMessage());
        }
    }

    /**
     * Get user activity data for AJAX
     */
    public function getActivity(User $user)
    {
        try {
            $activity = [];

            // Email campaigns
            $campaigns = $user->emailCampaigns()->latest()->take(10)->get();
            foreach ($campaigns as $campaign) {
                $activity[] = [
                    'type' => 'email_campaign',
                    'title' => "Created email campaign: {$campaign->name}",
                    'date' => $campaign->created_at->toISOString(),
                    'formatted_date' => $campaign->created_at->diffForHumans(),
                    'icon' => 'fas fa-envelope',
                    'color' => 'text-primary',
                ];
            }

            // Contacts created
            $contacts = $user->contactsCreated()->latest()->take(10)->get();
            foreach ($contacts as $contact) {
                $activity[] = [
                    'type' => 'contact',
                    'title' => "Created contact: {$contact->first_name} {$contact->last_name}",
                    'date' => $contact->created_at->toISOString(),
                    'formatted_date' => $contact->created_at->diffForHumans(),
                    'icon' => 'fas fa-user-plus',
                    'color' => 'text-success',
                ];
            }

            // Sort by date descending
            usort($activity, function ($a, $b) {
                return strtotime($b['date']) - strtotime($a['date']);
            });

            return response()->json([
                'success' => true,
                'activity' => array_slice($activity, 0, 20),
            ]);

        } catch (\Exception $e) {
            Log::error('Get user activity failed: '.$e->getMessage());

            return response()->json(['error' => 'Failed to load activity.'], 500);
        }
    }
}
