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
}
