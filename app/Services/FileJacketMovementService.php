<?php

namespace App\Services;

use App\Models\FileJacket;
use App\Models\FileJacketMovement;
use App\Models\FileRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FileJacketMovementService
{
    /**
     * Dispatch a jacket to another department/user.
     */
    public function dispatchJacket(
        int $jacketId,
        int $toDepartmentId,
        ?int $toUserId = null,
        ?string $remarks = null
    ): FileJacketMovement {
        return DB::transaction(function () use ($jacketId, $toDepartmentId, $toUserId, $remarks) {
            $jacket = FileJacket::lockForUpdate()->findOrFail($jacketId);
            $user = Auth::user();

            // Validations
            if (!in_array($jacket->status, ['active'])) {
                throw new \Exception('Only active jackets can be dispatched.');
            }
            if ($jacket->current_department_id !== $user->department_id) {
                throw new \Exception('You can only dispatch jackets from your department.');
            }
            if ($jacket->hasPendingDispatch()) {
                throw new \Exception('This jacket already has a pending dispatch.');
            }

            // Create movement record
            $movement = FileJacketMovement::create([
                'jacket_id' => $jacket->id,
                'from_department_id' => $user->department_id,
                'from_user_id' => $user->id,
                'to_department_id' => $toDepartmentId,
                'to_user_id' => $toUserId,
                'dispatched_at' => now(),
                'status' => 'PENDING_RECEIPT',
                'dispatched_by' => $user->id,
                'remarks' => $remarks,
            ]);

            // Update jacket state
            $jacket->update([
                'current_holder_user_id' => null,
                'status' => 'in_transit',
            ]);

            // Audit log
            DB::table('audit_logs')->insert([
                'agency_id' => 1,
                'action_type' => 'JACKET_DISPATCHED',
                'entity_type' => 'file_jackets',
                'entity_id' => $jacket->id,
                'new_values' => json_encode([
                    'movement_id' => $movement->id,
                    'to_department_id' => $toDepartmentId,
                    'to_user_id' => $toUserId,
                    'remarks' => $remarks,
                ]),
                'user_id' => $user->id,
                'ip_address' => request()->ip(),
                'created_at' => now(),
            ]);

            return $movement;
        });
    }

    /**
     * Receive a dispatched jacket.
     */
    public function receiveJacket(int $movementId): FileJacketMovement
    {
        return DB::transaction(function () use ($movementId) {
            $movement = FileJacketMovement::lockForUpdate()->findOrFail($movementId);
            $user = Auth::user();

            // Validations
            if ($movement->status !== 'PENDING_RECEIPT') {
                throw new \Exception('This movement is no longer pending.');
            }
            if ($movement->to_user_id !== null && $movement->to_user_id !== $user->id) {
                throw new \Exception('You are not the designated recipient.');
            }
            if ($movement->to_user_id === null && $user->department_id !== $movement->to_department_id) {
                throw new \Exception('You are not in the destination department.');
            }

            // Complete the movement
            $movement->update([
                'received_at' => now(),
                'received_by' => $user->id,
                'status' => 'RECEIVED',
            ]);

            // Update jacket location
            $jacket = $movement->jacket;
            $jacket->update([
                'current_department_id' => $movement->to_department_id,
                'current_holder_user_id' => $user->id,
                'status' => 'active',
            ]);

            // Synchronize document locations
            FileRecord::where('current_file_jacket_id', $jacket->id)
                ->update([
                    'current_department_id' => $movement->to_department_id,
                    'current_holder_user_id' => $user->id,
                ]);

            // Audit log
            DB::table('audit_logs')->insert([
                'agency_id' => 1,
                'action_type' => 'JACKET_RECEIVED',
                'entity_type' => 'file_jackets',
                'entity_id' => $jacket->id,
                'new_values' => json_encode([
                    'movement_id' => $movement->id,
                    'received_by' => $user->id,
                    'documents_synced' => FileRecord::where('current_file_jacket_id', $jacket->id)->count(),
                ]),
                'user_id' => $user->id,
                'ip_address' => request()->ip(),
                'created_at' => now(),
            ]);

            return $movement;
        });
    }
}
