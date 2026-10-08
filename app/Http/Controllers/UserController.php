<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Branch;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with('branch')->orderBy('role')->orderBy('name');

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%");
            });
        }

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        $users = $query->paginate(25);
        return view('users.index', compact('users'));
    }

    public function create()
    {
        $branches = Branch::active()->orderBy('name')->get();
        return view('users.create', compact('branches'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'role' => 'required|in:cashier,manager,owner',
            'branch_id' => [
                Rule::requiredIf(fn() => $request->role === 'cashier'),
                'nullable',
                'exists:branches,id',
            ],
        ]);

        $branchId = in_array($request->role, ['owner', 'manager']) ? null : $request->branch_id;

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'role' => $request->role,
            'branch_id' => $branchId,
        ]);

        AuditLog::log('user_created', 'users', "User '{$user->name}' created with role {$user->role}", null, 'user', $user->id);

        return redirect()->route('users.index')->with('success', 'User created successfully.');
    }

    public function edit(User $user)
    {
        $branches = Branch::active()->orderBy('name')->get();
        return view('users.edit', compact('user', 'branches'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role' => 'required|in:cashier,manager,owner',
            'branch_id' => [
                Rule::requiredIf(fn() => $request->role === 'cashier'),
                'nullable',
                'exists:branches,id',
            ],
            'is_active' => 'nullable|boolean',
            'password' => 'nullable|string|min:6|confirmed',
        ]);

        $newBranchId = in_array($request->role, ['owner', 'manager']) ? null : ($request->branch_id ? (int)$request->branch_id : null);
        $oldBranchId = $user->branch_id ? (int)$user->branch_id : null;

        // Block branch change if the user has an active open shift
        if ($oldBranchId !== $newBranchId) {
            $hasOpenShift = Shift::where('status', 'open')
                ->where(function ($q) use ($user) {
                    $q->where('authorized_by', $user->id)
                      ->orWhere('opened_by', $user->name);
                })->exists();

            if ($hasOpenShift) {
                return back()->withInput()->withErrors([
                    'branch_id' => "Cannot change branch while staff member '{$user->name}' has an active open shift. Please reconcile and end the shift first."
                ]);
            }

            $oldBranchName = $user->branch?->name ?? 'All branches (owner access)';
            $newBranchName = $newBranchId ? Branch::find($newBranchId)?->name : 'All branches (owner access)';

            AuditLog::log(
                'user_branch_updated',
                'users',
                "Assigned branch for staff '{$user->name}' changed from {$oldBranchName} to {$newBranchName}",
                $user,
                'user',
                $user->id,
                [
                    'before' => ['branch_id' => $oldBranchId, 'branch' => $oldBranchName],
                    'after' => ['branch_id' => $newBranchId, 'branch' => $newBranchName]
                ]
            );
        }

        $updateData = [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'branch_id' => $newBranchId,
        ];

        if ($request->has('is_active') && $user->id !== auth()->id()) {
            $updateData['is_active'] = (bool) $request->is_active;
        }

        $user->update($updateData);

        if ($request->filled('password')) {
            $user->update(['password' => bcrypt($request->password)]);
        }

        AuditLog::log('user_updated', 'users', "User '{$user->name}' updated", null, 'user', $user->id);

        return redirect()->route('users.index')->with('success', 'User updated successfully.');
    }

    public function toggleActive(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot deactivate your own account.');
        }

        $user->update(['is_active' => !$user->is_active]);
        $status = $user->is_active ? 'activated' : 'deactivated';

        AuditLog::log("user_{$status}", 'users', "User '{$user->name}' {$status}", null, 'user', $user->id);

        return back()->with('success', "User {$status} successfully.");
    }
}
