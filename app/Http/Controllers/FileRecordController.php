<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FileRecord;

class FileRecordController extends Controller
{
    /**
     * Display the specified file record and its entire movement history.
     */
    public function show(FileRecord $file)
    {
        // Eager load everything needed for the timeline view
        $file->load([
            'status',
            'originatingDepartment',
            'currentDepartment',
            'currentOwner',
            'movements' => function($q) {
                // Order from newest to oldest for the timeline
                $q->with(['fromUser.department', 'fromDepartment', 'toUser.department', 'toDepartment'])
                  ->orderBy('dispatched_at', 'desc');
            }
        ]);

        return view('files.show', compact('file'));
    }

    /**
     * Update the physical file record attributes.
     */
    public function update(Request $request, FileRecord $file)
    {
        if ($file->current_owner_id !== \Illuminate\Support\Facades\Auth::id()) {
            abort(403, 'Unauthorized. Only the active custodian can update file properties.');
        }

        $validated = $request->validate([
            'status_id' => 'required|exists:statuses,id',
            'title' => 'required|string|max:255',
            'department_id' => 'required|exists:departments,id',
            'priority_level' => 'required|integer|min:1|max:5',
            'confidentiality_level' => 'required|integer|min:1|max:5',
        ]);
        
        $file->update([
            'status_id' => $validated['status_id'],
            'title' => $validated['title'],
            'originating_department_id' => $validated['department_id'],
            'priority_level' => $validated['priority_level'],
            'confidentiality_level' => $validated['confidentiality_level'],
        ]);

        return redirect()->route('files.show', $file)->with('success', 'File metadata updated successfully.');
    }
}
