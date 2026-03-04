<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SystemSettingController extends Controller
{
    public function index()
    {
        $setting = \App\Models\SystemSetting::where('key', 'system_logo_path')->first();
        return view('admin.settings.index', compact('setting'));
    }

    public function updateLogo(Request $request)
    {
        $request->validate([
            'logo' => ['required', 'image', 'mimes:jpeg,png,jpg,svg', 'max:2048'],
        ]);

        $setting = \App\Models\SystemSetting::firstOrCreate(['key' => 'system_logo_path']);

        if ($setting->value) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($setting->value);
        }

        $path = $request->file('logo')->store('system-logos', 'public');
        
        $setting->update(['value' => $path]);

        return redirect()->back()->with('success', 'System logo updated successfully.');
    }
}
