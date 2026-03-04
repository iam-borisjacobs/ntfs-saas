<?php

namespace App\Services;

use App\Models\Department;
use App\Models\Status;
use Illuminate\Support\Facades\Cache;

class CacheService
{
    /**
     * Retrieve all departments, utilizing Cache to prevent repeated DB hits.
     */
    public function getDepartments()
    {
        return Cache::remember('departments.all', now()->addHours(24), function () {
            return Department::orderBy('name')->get();
        });
    }

    /**
     * Retrieve all statuses, utilizing Cache to prevent repeated DB hits.
     */
    public function getStatuses()
    {
        return Cache::remember('statuses.all', now()->addHours(24), function () {
            return Status::orderBy('id')->get();
        });
    }

    /**
     * Clear reference caches manually when needed via Admin.
     */
    public function clearReferenceCaches(): void
    {
        Cache::forget('departments.all');
        Cache::forget('statuses.all');
    }
}
