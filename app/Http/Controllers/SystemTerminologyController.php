<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SystemTerminology;

class SystemTerminologyController extends Controller
{
    public function index()
    {
        $terminologies = SystemTerminology::orderBy('group_name')->orderBy('key')->get();
        // If empty, we can seed defaults on the fly or they will just see an empty UI
        if($terminologies->isEmpty()){
            SystemTerminology::insert([
                ['key' => 'file_jacket', 'value' => 'File Jacket', 'default_value' => 'File Jacket', 'description' => 'Used for document container names', 'group_name' => 'Entities'],
                ['key' => 'file_jackets', 'value' => 'File Jackets', 'default_value' => 'File Jackets', 'description' => 'Plural version of File Jacket', 'group_name' => 'Entities'],
                ['key' => 'department', 'value' => 'Department', 'default_value' => 'Department', 'description' => 'Used for structural organizational units', 'group_name' => 'Entities'],
                ['key' => 'station', 'value' => 'Geographic Station', 'default_value' => 'Geographic Station', 'description' => 'Branch or Station location', 'group_name' => 'Entities'],
                ['key' => 'agency_abbr', 'value' => 'AGENCY', 'default_value' => 'AGENCY', 'description' => 'The short abbreviation shown on the sidebar and logos', 'group_name' => 'General'],
            ]);
            $terminologies = SystemTerminology::orderBy('group_name')->orderBy('key')->get();
        }

        return view('admin.settings.terminology', compact('terminologies'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'terms' => 'required|array',
            'terms.*.value' => 'required|string|max:255',
        ]);

        foreach ($request->terms as $id => $data) {
            SystemTerminology::where('id', $id)->update(['value' => $data['value']]);
        }

        // Mass updates via query builder bypass model events, so we MUST manually clear the cache
        \Illuminate\Support\Facades\Cache::forget('system_terminologies');

        return redirect()->back()->with('success', 'Terminology mappings updated successfully! Refresh other tabs to see changes.');
    }
}
