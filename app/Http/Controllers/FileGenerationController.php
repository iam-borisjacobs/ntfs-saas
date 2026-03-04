<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Department;
use App\Models\FileRecord;
use App\Models\Status;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class FileGenerationController extends Controller
{
    /**
     * Show the form for creating a new file.
     */
    public function create()
    {
        $departments = Department::orderBy('name')->get();
        return view('files.create', compact('departments'));
    }

    /**
     * Store a newly created file in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'department_id' => 'required|exists:departments,id',
            'priority_level' => 'required|integer|in:1,2,3',
            'confidentiality_level' => 'required|integer|in:1,2,3',
        ]);

        DB::beginTransaction();
        try {
            $status = Status::where('name', 'RECEIVED')->firstOrFail();

            // Generate a unique File Reference Number (simulated logic for now)
            $refNumber = 'NAMA/' . date('Y') . '/' . strtoupper(Str::random(6));

            $file = FileRecord::create([
                'uuid' => (string) Str::uuid(),
                'file_reference_number' => $refNumber,
                'title' => $validated['title'],
                'originating_department_id' => $validated['department_id'],
                'current_department_id' => $validated['department_id'],
                'current_owner_id' => Auth::id(),
                'status_id' => $status->id,
                'priority_level' => $validated['priority_level'],
                'confidentiality_level' => $validated['confidentiality_level'],
            ]);

            // Create the Genesis movement ledger entry
            $file->movements()->create([
                'request_uuid' => (string) Str::uuid(),
                'from_user_id' => Auth::id(),
                'to_user_id' => Auth::id(), // Starts on the creator's desk
                'from_department_id' => $validated['department_id'],
                'to_department_id' => $validated['department_id'],
                'movement_type' => 'CREATION',
                'remarks' => 'File physically generated and logged into the system.',
                'acknowledgment_status' => 'ACCEPTED', // Genesis is auto-acknowledged
                'received_at' => now(),
            ]);

            DB::commit();

            return redirect()->route('queues.pending')->with('success', 'File generated successfully: ' . $refNumber);

        } catch (\Exception $e) {
            DB::rollBack();
            \Illuminate\Support\Facades\Log::error('File Generation Error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return back()->withInput()->withErrors(['error' => 'Failed to generate file record: ' . $e->getMessage()]);
        }
    }
}
