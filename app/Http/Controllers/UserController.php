<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query()->orderBy('role')->orderBy('name');

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
        return view('users.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'role' => 'required|in:cashier,supervisor,manager,owner',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'role' => $request->role,
        ]);

        AuditLog::log('user_created', 'users', "User '{$user->name}' created with role {$user->role}", null, 'user', $user->id);

        return redirect()->route('users.index')->with('success', 'User created successfully.');
    }

    public function edit(User $user)
    {
        return view('users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role' => 'required|in:cashier,supervisor,manager,owner',
            'is_active' => 'nullable|boolean',
            'password' => 'nullable|string|min:6|confirmed',
        ]);

        $updateData = [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
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
