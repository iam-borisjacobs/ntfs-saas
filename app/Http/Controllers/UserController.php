<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Department;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with(['department', 'roles'])->orderBy('name')->paginate(25);
        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        $departments = Department::orderBy('name')->get();
        // Get all Spatie roles
        $roles = \Spatie\Permission\Models\Role::orderBy('name')->get();
        return view('admin.users.create', compact('departments', 'roles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'system_identifier' => ['required', 'string', 'max:50', 'unique:users'],
            'department_id' => ['required', 'exists:departments,id'],
            'clearance_level' => ['required', 'integer', 'in:1,2,3'],
            'is_active' => ['required', 'boolean'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'exists:roles,name'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'system_identifier' => $validated['system_identifier'],
            'department_id' => $validated['department_id'],
            'clearance_level' => $validated['clearance_level'],
            'is_active' => $validated['is_active'],
            'password' => Hash::make($validated['password']),
        ]);

        $user->assignRole($validated['role']);

        return redirect()->route('admin.users.index')->with('success', 'User onboarded successfully.');
    }

    public function edit(User $user)
    {
        $departments = Department::orderBy('name')->get();
        $roles = \Spatie\Permission\Models\Role::orderBy('name')->get();
        return view('admin.users.edit', compact('user', 'departments', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'system_identifier' => ['required', 'string', 'max:50', 'unique:users,system_identifier,'.$user->id],
            'department_id' => ['required', 'exists:departments,id'],
            'clearance_level' => ['required', 'integer', 'in:1,2,3'],
            'is_active' => ['required', 'boolean'],
            'role' => ['required', 'exists:roles,name'],
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'system_identifier' => $validated['system_identifier'],
            'department_id' => $validated['department_id'],
            'clearance_level' => $validated['clearance_level'],
            'is_active' => $validated['is_active'],
        ]);

        // Sync roles
        $user->syncRoles([$validated['role']]);

        // Option to reset password
        if ($request->filled('password')) {
            $request->validate([
                'password' => ['confirmed', Rules\Password::defaults()],
            ]);
            $user->update(['password' => Hash::make($request->password)]);
        }

        return redirect()->route('admin.users.index')->with('success', 'User updated successfully.');
    }
}
