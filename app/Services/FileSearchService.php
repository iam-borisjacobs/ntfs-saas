<?php

namespace App\Services;

use App\Models\FileRecord;
use App\Models\FileMovement;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

class FileSearchService
{
    /**
     * The core RBAC query builder that ALL search operations MUST use.
     * Prevents data leakage by stripping out files above the user's clearance.
     */
    public function getRbacBaseQuery()
    {
        $user = Auth::user();

        // 1. Start with the core security constraint
        $query = FileRecord::where('confidentiality_level', '<=', $user->clearance_level);

        // 2. Further scoping can occur here based on roles (e.g., if departmentally restricted)
        // For NAMA, users can see files within their clearance, but editing/actioning is restricted elsewhere.
        // If strict departmental silo is needed:
        // if (!$user->hasRole('Sys Admin') && !$user->hasRole('Director')) {
        //     $query->where('current_department_id', $user->department_id);
        // }

        return $query;
    }

    /**
     * Executes the Quick Search across exact reference match or partial title match.
     */
    public function executeQuickSearch(string $term)
    {
        $term = trim($term);
        if (empty($term)) {
            return collect(); // Empty
        }

        // Limit wildcard abuse
        $term = str_replace('%', '', $term);

        return $this->getRbacBaseQuery()
            ->where(function (Builder $q) use ($term) {
                // 1. Exact Reference Match (B-Tree index) prioritised
                $q->where('file_reference_number', $term)
                  // 2. Partial Title match (GIN Trigram index via raw whereRaw)
                  ->orWhereRaw("title ILIKE ?", ["%{$term}%"]);
            })
            ->with(['status', 'currentDepartment', 'currentOwner'])
            ->orderByRaw("CASE WHEN file_reference_number = ? THEN 1 ELSE 2 END", [$term])
            ->paginate(25);
    }

    /**
     * Executes the Advanced Filter Search
     */
    public function executeAdvancedFilters(array $filters, bool $export = false)
    {
        $query = $this->getRbacBaseQuery();

        if (!empty($filters['status_id'])) {
            $query->whereIn('status_id', (array) $filters['status_id']);
        }

        if (!empty($filters['department_id'])) {
            $query->where('current_department_id', $filters['department_id']);
        }

        if (!empty($filters['owner_id'])) {
            $query->where('current_owner_id', $filters['owner_id']);
        }

        if (isset($filters['priority_level']) && $filters['priority_level'] !== '') {
            $query->where('priority_level', $filters['priority_level']);
        }

        // Must still obey clearance level constraint, but user can filter downward
        if (isset($filters['clearance_level']) && $filters['clearance_level'] !== '') {
            $reqClearance = (int) $filters['clearance_level'];
            if ($reqClearance <= Auth::user()->clearance_level) {
                $query->where('clearance_level', $reqClearance);
            }
        }

        if (!empty($filters['date_from']) && !empty($filters['date_to'])) {
            $query->whereBetween('created_at', [$filters['date_from'], $filters['date_to']]);
        }

        // Has Pending Movement Filter
        if (!empty($filters['has_pending'])) {
            $query->whereHas('movements', function (Builder $q) {
                $q->where('acknowledgment_status', 'PENDING');
            });
        }

        // Escalated Filter
        if (!empty($filters['is_escalated'])) {
            $query->whereHas('movements', function (Builder $q) {
                $q->where('is_escalated', true);
            });
        }

        // Archived Opt-in filter (default is to exclude Terminal states like ARCHIVED and CLOSED, unless specifically requested)
        if (empty($filters['include_archived'])) {
            $query->whereHas('status', function (Builder $q) {
                $q->where('is_terminal', false);
            });
        }

        // Sort
        $query->orderBy('created_at', 'desc');

        if ($export) {
            return $query;
        }

        // Eager load for presentation
        $query->with(['status', 'currentDepartment', 'currentOwner']);
        
        return $query->paginate(25);
    }
}
