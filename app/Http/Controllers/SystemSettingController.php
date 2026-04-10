<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SystemSettingController extends Controller
{
    public function index()
    {
        $setting = \App\Models\SystemSetting::where('key', 'system_logo_path')->first();
        $digitalModuleEnabled = \App\Models\SystemSetting::where('key', 'digital_module_enabled')->value('value') ?? 'true';
        $systemTitle = \App\Models\SystemSetting::where('key', 'system_title')->value('value') ?: config('app.name', 'Laravel');
        $systemFavicon = \App\Models\SystemSetting::where('key', 'system_favicon_path')->first();
        $systemAuthorName = \App\Models\SystemSetting::where('key', 'system_author_name')->value('value') ?: 'NAMA NG';
        $systemGuestHeader = \App\Models\SystemSetting::where('key', 'system_guest_header')->value('value') ?: 'Secure Document Tracking Portal';
        $systemGuestDescription = \App\Models\SystemSetting::where('key', 'system_guest_description')->value('value') ?: 'Authorized personnel only. Access the central registry to dispatch, track, and acknowledge critical documentation sequences across all inter-departmental desks.';
        $primaryColorHex = \App\Models\SystemSetting::where('key', 'primary_color_hex')->value('value') ?: '#003B73'; // Default to the original dark blue

        return view('admin.settings.index', compact('setting', 'digitalModuleEnabled', 'systemTitle', 'systemFavicon', 'systemAuthorName', 'systemGuestHeader', 'systemGuestDescription', 'primaryColorHex'));
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

    public function updateDigitalModule(Request $request)
    {
        $request->validate([
            'digital_module_enabled' => 'required|in:true,false',
        ]);

        \App\Models\SystemSetting::updateOrCreate(
            ['key' => 'digital_module_enabled'],
            ['value' => $request->digital_module_enabled]
        );

        return redirect()->back()->with('success', 'Digital Module configuration updated successfully.');
    }

    public function updateBasic(Request $request)
    {
        $request->validate([
            'system_title' => 'required|string|max:255',
            'system_author_name' => 'nullable|string|max:255',
            'system_guest_header' => 'nullable|string|max:255',
            'system_guest_description' => 'nullable|string|max:1000',
            'favicon' => 'nullable|image|mimes:ico,png,svg,jpg,jpeg|max:1024',
            'primary_color_hex' => ['nullable', 'string', 'regex:/^#([a-fA-F0-9]{6}|[a-fA-F0-9]{3})$/'],
        ]);

        \App\Models\SystemSetting::updateOrCreate(
            ['key' => 'system_title'],
            ['value' => $request->system_title]
        );

        if ($request->filled('primary_color_hex')) {
            \App\Models\SystemSetting::updateOrCreate(['key' => 'primary_color_hex'], ['value' => $request->primary_color_hex]);
        }

        if ($request->filled('system_author_name')) {
            \App\Models\SystemSetting::updateOrCreate(['key' => 'system_author_name'], ['value' => $request->system_author_name]);
        }

        if ($request->filled('system_guest_header')) {
            \App\Models\SystemSetting::updateOrCreate(['key' => 'system_guest_header'], ['value' => $request->system_guest_header]);
        }

        if ($request->filled('system_guest_description')) {
            \App\Models\SystemSetting::updateOrCreate(['key' => 'system_guest_description'], ['value' => $request->system_guest_description]);
        }

        if ($request->hasFile('favicon')) {
            $setting = \App\Models\SystemSetting::firstOrCreate(['key' => 'system_favicon_path']);
            if ($setting->value) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($setting->value);
            }
            $path = $request->file('favicon')->store('system-logos', 'public');
            $setting->update(['value' => $path]);
        }

        return redirect()->back()->with('success', 'General system settings updated successfully.');
    }
}
