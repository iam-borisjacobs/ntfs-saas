<?php

namespace App\Services;

use App\Models\Status;
use App\Models\FileRecord;
use Illuminate\Support\Facades\DB;

class FileStateService
{
    /**
     * Check if a transition between two states is allowed based on the DB matrix.
     *
     * @param int $fromStatusId
     * @param int $toStatusId
     * @return bool
     */
    public function isTransitionAllowed(int $fromStatusId, int $toStatusId): bool
    {
        return DB::table('status_transitions')
            ->where('from_status_id', $fromStatusId)
            ->where('to_status_id', $toStatusId)
            ->exists();
    }

    /**
     * Cache and retrieve a Status ID by its unique string name to avoid hardcoding IDs.
     *
     * @param string $name
     * @return int
     * @throws \Exception
     */
    public function getStatusIdByName(string $name): int
    {
        $status = Status::where('name', $name)->first();

        if (!$status) {
            throw new \Exception("Canonical status [{$name}] does not exist in the database.");
        }

        return $status->id;
    }

    /**
     * Verify if the current state of a file represents a terminal state (cannot be mutated).
     *
     * @param int $statusId
     * @return bool
     */
    public function isTerminalState(int $statusId): bool
    {
        return Status::where('id', $statusId)->value('is_terminal') ?? false;
    }
}
