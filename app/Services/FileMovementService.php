<?php

namespace App\Services;

use App\Models\FileRecord;
use App\Models\FileMovement;
use Illuminate\Support\Facades\DB;
use App\Services\FileStateService;
use Exception;

class FileMovementService
{
    protected FileStateService $stateService;

    public function __construct(FileStateService $stateService)
    {
        $this->stateService = $stateService;
    }

    /**
     * Dispatch a file from the current owner to a new user.
     * Enforces pessimistic locking, idempotency, and strict rule validation.
     *
     * @param int $fileId
     * @param int|null $toUserId
     * @param int $toDepartmentId
     * @param string $remarks
     * @param string $requestUuid Idempotency Key
     * @return FileMovement
     * @throws Exception
     */
    public function dispatchFile(int $fileId, ?int $toUserId, int $toDepartmentId, string $remarks, string $requestUuid): FileMovement
    {
        return DB::transaction(function () use ($fileId, $toUserId, $toDepartmentId, $remarks, $requestUuid) {
            
            // 1. Pessimistic Lock on the primary resource
            $file = FileRecord::where('id', $fileId)->lockForUpdate()->firstOrFail();

            // 2. Validate Ownership (Authorization)
            if ($file->current_owner_id !== \Illuminate\Support\Facades\Auth::id()) {
                throw new Exception("You do not have physical custody of this file.");
            }

            // 3. Validate Terminal State
            if ($this->stateService->isTerminalState($file->status_id)) {
                throw new Exception("This file is in a terminal state and cannot be dispatched.");
            }

            // 4. Ensure Idempotency (prevent duplicate DB inserts from double-clicks)
            $existingMovement = FileMovement::where('request_uuid', $requestUuid)->first();
            if ($existingMovement) {
                return $existingMovement; // Gracefully handle idempotency
            }

            // 5. Validate Duplicate/Pending Movements
            $hasPending = FileMovement::where('file_id', $fileId)
                ->where('acknowledgment_status', 'PENDING')
                ->exists();
                
            if ($hasPending) {
                throw new Exception("This file already has a pending dispatch awaiting acknowledgment.");
            }

            // 6. Resolve Target Status
            $inTransitStatusId = $this->stateService->getStatusIdByName('IN_TRANSIT');

            // 7. Verify Transition is legally allowed
            if (!$this->stateService->isTransitionAllowed($file->status_id, $inTransitStatusId)) {
                $check = \Illuminate\Support\Facades\DB::table('status_transitions')
                    ->where('from_status_id', $file->status_id)
                    ->where('to_status_id', $inTransitStatusId)
                    ->exists();
                dd("Checking transition: ", $file->status_id, $inTransitStatusId, $check);
                throw new Exception("Invalid state transition to IN_TRANSIT.");
            }

            // 8. Determine movement type
            $movementType = $toUserId ? 'DISPATCH' : 'DEPARTMENT_INBOX';

            // 9. Insert Movement Record
            $movement = FileMovement::create([
                'agency_id' => $file->agency_id,
                'file_id' => $file->id,
                'from_user_id' => \Illuminate\Support\Facades\Auth::id(),
                'from_department_id' => $file->current_department_id,
                'to_user_id' => $toUserId,
                'to_department_id' => $toDepartmentId,
                'movement_type' => $movementType,
                'acknowledgment_status' => 'PENDING',
                'remarks' => $remarks,
                'request_uuid' => $requestUuid,
            ]);

            // 9. Update File Status (Leave current owner intact until received)
            $oldStatusId = $file->status_id;
            $file->status_id = $inTransitStatusId;
            $file->save();

            // 11. Manual Audit Log for Dispatch
            $toDepartment = \App\Models\Department::find($toDepartmentId);
            $auditDetail = $toUserId
                ? 'Sent to ' . (\App\Models\User::find($toUserId)->name ?? 'Unknown') . ' (' . ($toDepartment->name ?? 'Unknown') . ')'
                : 'Sent to ' . ($toDepartment->name ?? 'Unknown') . ' Department Inbox';

            DB::table('audit_logs')->insert([
                'agency_id' => $file->agency_id,
                'action_type' => 'DISPATCH',
                'entity_type' => 'file_movements',
                'entity_id' => $movement->id,
                'old_values' => json_encode(['status_id' => $oldStatusId]),
                'new_values' => json_encode(['status_id' => $inTransitStatusId, 'to_user_id' => $toUserId, 'detail' => $auditDetail]),
                'user_id' => \Illuminate\Support\Facades\Auth::id(),
                'ip_address' => request()->ip(),
                'created_at' => now(),
            ]);

            return $movement;
        });
    }

    /**
     * Accept a pending file transfer, legally claiming custody.
     *
     * @param int $movementId
     * @param int|null $fileJacketId Optional jacket to file the document into on receipt
     * @return FileMovement
     * @throws Exception
     */
    public function receiveFile(int $movementId, ?int $fileJacketId = null): FileMovement
    {
        return DB::transaction(function () use ($movementId, $fileJacketId) {
            
            // Look up movement ID (non-blocking) just to get the explicitly protected file_id
            $unlockedMovement = FileMovement::where('id', $movementId)->firstOrFail();

            // 1. pessimistic lock on file FIRST (Deterministic Locking Order)
            $file = FileRecord::where('id', $unlockedMovement->file_id)->lockForUpdate()->firstOrFail();

            // 2. pessimistic lock on movement
            $movement = FileMovement::where('id', $movementId)->lockForUpdate()->firstOrFail();

            if ($movement->acknowledgment_status !== 'PENDING') {
                throw new Exception("This movement is no longer pending.");
            }

            // Authorization: Direct dispatch requires exact user match;
            // Department inbox requires department membership.
            $currentUserId = \Illuminate\Support\Facades\Auth::id();
            if ($movement->to_user_id !== null) {
                // Direct dispatch — only the designated recipient can accept
                if ($movement->to_user_id !== $currentUserId) {
                    throw new Exception("You are not the designated recipient of this file.");
                }
            } else {
                // Department inbox — any active member of the destination department can accept
                $userDeptId = \Illuminate\Support\Facades\Auth::user()->department_id;
                if ($userDeptId !== $movement->to_department_id) {
                    throw new Exception("You are not a member of the destination department.");
                }
            }

            // 3. Resolve Target Status
            $receivedStatusId = $this->stateService->getStatusIdByName('RECEIVED');

            // 4. For department inbox dispatches, assign the receiver FIRST (while still PENDING)
            if ($movement->to_user_id === null) {
                $movement->to_user_id = $currentUserId;
            }

            // 5. Update Movement Ledger (single atomic save)
            $movement->acknowledgment_status = 'ACCEPTED';
            $movement->received_at = now();
            $movement->file_jacket_id = $fileJacketId;
            $movement->save();

            // 6. Update File Custody
            $file->current_owner_id = $movement->to_user_id;
            $file->current_department_id = $movement->to_department_id;
            $file->status_id = $receivedStatusId;
            $file->current_file_jacket_id = $fileJacketId;
            $file->save();

            // 7. Audit Log
            DB::table('audit_logs')->insert([
                'agency_id' => $file->agency_id,
                'action_type' => 'RECEIVE',
                'entity_type' => 'file_movements',
                'entity_id' => $movement->id,
                'new_values' => json_encode([
                    'acknowledgment_status' => 'ACCEPTED',
                    'file_jacket_id' => $fileJacketId,
                ]),
                'user_id' => \Illuminate\Support\Facades\Auth::id(),
                'ip_address' => request()->ip(),
                'created_at' => now(),
            ]);

            return $movement;
        });
    }

    /**
     * Reject a pending file transfer and return custody to the sender.
     *
     * @param int $movementId
     * @param string $rejectionReason
     * @return FileMovement The newly created return movement
     * @throws Exception
     */
    public function rejectFile(int $movementId, string $rejectionReason): FileMovement
    {
        if (empty(trim($rejectionReason))) {
            throw new Exception("A detailed rejection reason must be provided.");
        }

        return DB::transaction(function () use ($movementId, $rejectionReason) {
            
            // Look up file_id prior to lock boundary
            $unlockedMovement = FileMovement::where('id', $movementId)->firstOrFail();

            // 1. Lock file row FIRST
            $file = FileRecord::where('id', $unlockedMovement->file_id)->lockForUpdate()->firstOrFail();

            // 2. Lock original movement
            $movement = FileMovement::where('id', $movementId)->lockForUpdate()->firstOrFail();

            if ($movement->acknowledgment_status !== 'PENDING') {
                throw new Exception("Only pending movements can be rejected.");
            }

            if ($movement->to_user_id !== null) {
                if ($movement->to_user_id !== \Illuminate\Support\Facades\Auth::id()) {
                    throw new Exception("You are not the designated recipient of this file.");
                }
            } else {
                $userDeptId = \Illuminate\Support\Facades\Auth::user()->department_id;
                if ($userDeptId !== $movement->to_department_id) {
                    throw new Exception("You are not a member of the destination department.");
                }
            }

            // 3. Resolve status
            $rejectedStatusId = $this->stateService->getStatusIdByName('REJECTED');

            // 4. Mark original transfer as rejected
            $movement->acknowledgment_status = 'REJECTED';
            $movement->remarks = "REJECTED: " . $rejectionReason;
            $movement->save();

            // 5. Insert automatic custody reversal row
            $returnMovement = FileMovement::create([
                'agency_id' => $file->agency_id,
                'file_id' => $file->id,
                'from_user_id' => \Illuminate\Support\Facades\Auth::id(),
                'from_department_id' => $movement->to_department_id,
                'to_user_id' => $movement->from_user_id,
                'to_department_id' => $movement->from_department_id,
                'movement_type' => 'RETURN',
                'dispatched_at' => now(),
                'received_at' => now(), // Automatically return to sender without secondary confirmation needed
                'acknowledgment_status' => 'ACCEPTED',
                'remarks' => 'SYSTEM GENERATED: Automatic Reversal due to Rejection.',
                'request_uuid' => \Illuminate\Support\Str::uuid()->toString(),
            ]);

            // 6. Revert File Custody
            $file->current_owner_id = $movement->from_user_id;
            $file->current_department_id = $movement->from_department_id;
            $file->status_id = $rejectedStatusId;
            $file->save();

            // 7. Audit Log
            DB::table('audit_logs')->insert([
                'agency_id' => $file->agency_id,
                'action_type' => 'REJECT',
                'entity_type' => 'file_movements',
                'entity_id' => $movement->id,
                'new_values' => json_encode(['rejection_reason' => $rejectionReason]),
                'user_id' => \Illuminate\Support\Facades\Auth::id(),
                'ip_address' => request()->ip(),
                'created_at' => now(),
            ]);

            return $returnMovement;
        });
    }

    /**
     * Close a movement chain after the document has been received.
     *
     * @param int $movementId
     * @param string|null $closureReason
     * @return FileMovement
     * @throws Exception
     */
    public function closeMovement(int $movementId, ?string $closureReason = null): FileMovement
    {
        return DB::transaction(function () use ($movementId, $closureReason) {

            $unlockedMovement = FileMovement::where('id', $movementId)->firstOrFail();

            // 1. Pessimistic lock on file FIRST
            $file = FileRecord::where('id', $unlockedMovement->file_id)->lockForUpdate()->firstOrFail();

            // 2. Pessimistic lock on movement
            $movement = FileMovement::where('id', $movementId)->lockForUpdate()->firstOrFail();

            // 3. Validate: must have been received
            if ($movement->received_at === null) {
                throw new Exception("Document must be received before it can be closed.");
            }

            // 4. Validate: not already closed
            if ($movement->movement_closed) {
                throw new Exception("This movement is already closed.");
            }

            // 5. Authorization: only the receiver can close
            $currentUserId = \Illuminate\Support\Facades\Auth::id();
            if ($movement->to_user_id !== $currentUserId) {
                throw new Exception("Only the receiver of the document may close this movement.");
            }

            // 6. Resolve target status
            $closedStatusId = $this->stateService->getStatusIdByName('CLOSED');

            // 7. Verify transition is allowed
            if (!$this->stateService->isTransitionAllowed($file->status_id, $closedStatusId)) {
                throw new Exception("Invalid state transition to CLOSED from current status.");
            }

            // 8. Close the movement (trigger allows this specific update on ACCEPTED movements)
            $movement->movement_closed = true;
            $movement->closed_at = now();
            $movement->closed_by = $currentUserId;
            $movement->closure_reason = $closureReason;
            $movement->save();

            // 9. Update file status to CLOSED
            $oldStatusId = $file->status_id;
            $file->status_id = $closedStatusId;
            $file->closed_at = now();
            $file->save();

            // 10. Audit log
            $auditDetail = $closureReason
                ? 'Closed with reason: "' . $closureReason . '"'
                : 'Document movement closed by receiver';

            DB::table('audit_logs')->insert([
                'agency_id' => $file->agency_id,
                'action_type' => 'CLOSE',
                'entity_type' => 'file_movements',
                'entity_id' => $movement->id,
                'old_values' => json_encode(['status_id' => $oldStatusId]),
                'new_values' => json_encode([
                    'status_id' => $closedStatusId,
                    'movement_closed' => true,
                    'detail' => $auditDetail,
                ]),
                'user_id' => $currentUserId,
                'ip_address' => request()->ip(),
                'created_at' => now(),
            ]);

            return $movement;
        });
    }
}
