<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $department = \App\Models\Department::firstOrCreate([
            'code' => 'GEN-OPS',
            'name' => 'General Operations'
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'department_id' => $department->id,
            'system_identifier' => 'UID-'.\Illuminate\Support\Str::random(6),
            'clearance_level' => 1,
            'is_active' => true,
        ]);
        
        // Give new web registrations standard base-level access
        if (\Spatie\Permission\Models\Role::where('name', 'Clerk')->exists() || \Spatie\Permission\Models\Role::count() === 0) {
            $role = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Clerk']);
            $user->assignRole($role);
        }

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
