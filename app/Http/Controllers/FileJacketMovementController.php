<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\FileJacket;
use App\Models\FileJacketMovement;
use App\Services\FileJacketMovementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class FileJacketMovementController extends Controller
{
    protected FileJacketMovementService $service;

    public function __construct(FileJacketMovementService $service)
    {
        $this->service = $service;
    }

    /**
     * Show dispatch form for a jacket.
     */
    public function createDispatch(FileJacket $jacket)
    {
        $user = Auth::user();
        if ($jacket->current_department_id !== $user->department_id) {
            abort(403, 'You can only dispatch jackets from your department.');
        }

        $departments = Department::where('id', '!=', $user->department_id)
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

        return view('file-jackets.dispatch', compact('jacket', 'departments'));
    }

    /**
     * Execute jacket dispatch.
     */
    public function storeDispatch(Request $request, FileJacket $jacket)
    {
        $request->validate([
            'to_department_id' => 'required|exists:departments,id',
            'to_user_id' => 'nullable|exists:users,id',
            'remarks' => 'nullable|string|max:2000',
        ]);

        try {
            $this->service->dispatchJacket(
                $jacket->id,
                (int) $request->to_department_id,
                $request->to_user_id ? (int) $request->to_user_id : null,
                $request->remarks
            );

            return redirect()->route('file-jackets.show', $jacket->id)
                ->with('success', 'Jacket dispatched successfully.');
        } catch (\Exception $e) {
            Log::error('Jacket Dispatch Error: ' . $e->getMessage());
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Show receive form for an incoming jacket.
     */
    public function showReceive(FileJacketMovement $movement)
    {
        $user = Auth::user();
        $movement->load(['jacket', 'fromUser', 'fromDepartment']);

        if ($movement->to_user_id !== null && $movement->to_user_id !== $user->id) {
            abort(403, 'You are not the designated recipient.');
        }
        if ($movement->to_user_id === null && $user->department_id !== $movement->to_department_id) {
            abort(403, 'You are not in the destination department.');
        }
        if ($movement->status !== 'PENDING_RECEIPT') {
            return redirect()->route('queues.incoming')
                ->withErrors(['error' => 'This movement is no longer pending.']);
        }

        return view('file-jackets.receive', compact('movement'));
    }

    /**
     * Execute jacket receipt.
     */
    public function receive(FileJacketMovement $movement)
    {
        try {
            $this->service->receiveJacket($movement->id);
            $jacket = $movement->jacket;
            $docCount = $jacket->currentFiles()->count();

            return redirect()->route('file-jackets.show', $jacket->id)
                ->with('success', "Jacket received successfully. {$docCount} document(s) inside the jacket are now available.");
        } catch (\Exception $e) {
            Log::error('Jacket Receive Error: ' . $e->getMessage());
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
