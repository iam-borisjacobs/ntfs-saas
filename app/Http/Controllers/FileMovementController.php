<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FileRecord;
use App\Models\User;
use App\Models\Department;
use App\Services\FileMovementService;
use Exception;
use Illuminate\Support\Str;

class FileMovementController extends Controller
{
    protected FileMovementService $movementService;

    public function __construct(FileMovementService $movementService)
    {
        $this->movementService = $movementService;
    }

    /**
     * Show the form for dispatching a physical file to another user.
     */
    public function createDispatch(FileRecord $file)
    {
        // Enforce physical custody logic
        if ($file->current_owner_id !== \Illuminate\Support\Facades\Auth::id()) {
            return redirect()->route('queues.pending')->withErrors(['error' => 'You do not have custody of this file.']);
        }

        // Only allow dispatch for files NOT in a terminal state
        if ($file->status->is_terminal) {
            return redirect()->route('queues.pending')->withErrors(['error' => 'Terminal status files cannot be dispatched.']);
        }

        // Fetch users and departments for the searchable dropdowns
        $users = User::with('department')->where('is_active', true)->where('id', '!=', \Illuminate\Support\Facades\Auth::id())->orderBy('name')->get();
        $departments = Department::orderBy('name')->get();

        return view('files.dispatch', compact('file', 'users', 'departments'));
    }

    /**
     * Store the dispatched payload and lock the file as IN_TRANSIT.
     */
    public function storeDispatch(Request $request, FileRecord $file)
    {
        $validated = $request->validate([
            'to_user_id' => 'required|exists:users,id',
            'to_department_id' => 'required|exists:departments,id',
            'remarks' => 'nullable|string|max:1000',
        ]);

        try {
            // Generate idempotency key internally (could also be passed from frontend)
            $idempotencyKey = $request->input('request_uuid', (string) Str::uuid());

            $this->movementService->dispatchFile(
                $file->id,
                $validated['to_user_id'],
                $validated['to_department_id'],
                $validated['remarks'] ?? '',
                $idempotencyKey
            );

            return redirect()->route('queues.outgoing')->with('success', 'File ' . $file->file_reference_number . ' successfully dispatched.');

        } catch (Exception $e) {
            \Illuminate\Support\Facades\Log::error('Dispatch Error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return back()->withInput()->withErrors(['error' => 'Dispatch failed: ' . $e->getMessage()]);
        }
    }

    /**
     * Accept custody of a dispatched file.
     */
    public function receive(\App\Models\FileMovement $movement)
    {
        try {
            $this->movementService->receiveFile($movement->id);
            return redirect()->route('queues.pending')->with('success', 'File custody accepted successfully.');
        } catch (Exception $e) {
            \Illuminate\Support\Facades\Log::error('Receive Error: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Failed to accept file: ' . $e->getMessage()]);
        }
    }

    /**
     * Reject custody of a dispatched file.
     */
    public function reject(Request $request, \App\Models\FileMovement $movement)
    {
        $request->validate(['rejection_reason' => 'required|string|max:1000']);
        try {
            $this->movementService->rejectFile($movement->id, $request->rejection_reason);
            return redirect()->route('queues.incoming')->with('success', 'File rejected and returned to sender.');
        } catch (Exception $e) {
            \Illuminate\Support\Facades\Log::error('Reject Error: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Failed to reject file: ' . $e->getMessage()]);
        }
    }
}
