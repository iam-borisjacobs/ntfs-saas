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

    public function bulk()
    {
        return view('admin.users.bulk');
    }

    public function processBulk(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:4096',
        ]);

        $file = fopen($request->file('csv_file')->getRealPath(), 'r');
        $header = fgetcsv($file);
        
        // Clean up header format (e.g. remove BOM if present)
        if($header && count($header)>0) {
            $header[0] = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $header[0]);
        }

        $successCount = 0;
        $errors = [];
        $index = 0;

        while (($row = fgetcsv($file)) !== false) {
            $index++;
            if (empty(array_filter($row)) || count($row) !== count($header)) continue;
            
            $data = array_combine($header, $row);
            
            $validator = \Illuminate\Support\Facades\Validator::make($data, [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
                'phone_number' => ['nullable', 'string', 'max:20'],
                'system_identifier' => ['required', 'string', 'max:50', 'unique:users'],
                'department_id' => ['required', 'exists:departments,id'],
                'clearance_level' => ['required', 'integer', 'in:1,2,3,4'],
                'role' => ['required', 'exists:roles,name'],
            ]);

            if ($validator->fails()) {
                $errors[] = "Row " . ($index + 1) . ": " . implode(", ", $validator->errors()->all());
                continue;
            }

            $password = \Illuminate\Support\Str::random(10);

            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone_number' => $data['phone_number'] ?? null,
                'system_identifier' => $data['system_identifier'],
                'department_id' => $data['department_id'],
                'clearance_level' => $data['clearance_level'],
                'is_active' => true,
                'password' => Hash::make($password),
            ]);

            $user->assignRole($data['role']);
            $successCount++;

            if (!empty($user->phone_number)) {
                $smsService = app(\App\Services\SmsService::class);
                $message = "Welcome to NTFS-SaaS! Your account has been provisioned.\nLogin ID: {$user->email}\nTemporary Password: {$password}\n\nPlease change this password upon your first login.";
                $smsService->sendWhatsApp($user->phone_number, $message);
            }
        }

        fclose($file);

        if (count($errors) > 0) {
            return redirect()->back()->with('warning', "$successCount users onboarded. We had issues with some rows.")->withErrors($errors);
        }

        return redirect()->route('admin.users.index')->with('success', "$successCount users bulk onboarded successfully.");
    }

    public function create()
    {
        $stations = \App\Models\Station::orderBy('name')->get();
        $departments = Department::orderBy('name')->get();
        // Get all Spatie roles
        $roles = \Spatie\Permission\Models\Role::orderBy('name')->get();
        return view('admin.users.create', compact('stations', 'departments', 'roles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'phone_number' => ['nullable', 'string', 'max:20'],
            'system_identifier' => ['required', 'string', 'max:50', 'unique:users'],
            'department_id' => ['required', 'exists:departments,id'],
            'clearance_level' => ['required', 'integer', 'in:1,2,3,4'],
            'is_active' => ['required', 'boolean'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'exists:roles,name'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone_number' => $validated['phone_number'],
            'system_identifier' => $validated['system_identifier'],
            'department_id' => $validated['department_id'],
            'clearance_level' => $validated['clearance_level'],
            'is_active' => $validated['is_active'],
            'password' => Hash::make($validated['password']),
        ]);

        $user->assignRole($validated['role']);

        if (!empty($user->phone_number)) {
            $smsService = app(\App\Services\SmsService::class);
            $message = "Welcome to NTFS-SaaS! Your account has been provisioned.\nLogin ID: {$user->email}\nPassword: {$validated['password']}\n\nPlease keep this secure and do not share it with anyone.";
            $smsService->sendWhatsApp($user->phone_number, $message);
        }

        return redirect()->route('admin.users.index')->with('success', 'User onboarded successfully.');
    }

    public function edit(User $user)
    {
        $stations = \App\Models\Station::orderBy('name')->get();
        $departments = Department::orderBy('name')->get();
        $roles = \Spatie\Permission\Models\Role::orderBy('name')->get();
        return view('admin.users.edit', compact('user', 'stations', 'departments', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'phone_number' => ['nullable', 'string', 'max:20'],
            'system_identifier' => ['required', 'string', 'max:50', 'unique:users,system_identifier,'.$user->id],
            'department_id' => ['required', 'exists:departments,id'],
            'clearance_level' => ['required', 'integer', 'in:1,2,3,4'],
            'is_active' => ['required', 'boolean'],
            'role' => ['required', 'exists:roles,name'],
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone_number' => $validated['phone_number'],
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
