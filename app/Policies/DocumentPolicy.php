<?php

namespace App\Policies;

use App\Models\Document;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class DocumentPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return config('digital_module.enabled', true);
    }

    /**
     * Determine whether the user can view the model (Download/Read).
     */
    public function view(User $user, Document $document): bool
    {
        if (!config('digital_module.enabled', true)) {
            return false;
        }

        // Global Admins can view anything
        if ($user->hasRole('System Administrator') || $user->hasRole('Registry Manager')) {
            return true;
        }

        // Must have sufficient clearance layer if linked to a file
        if ($document->fileRecord) {
            if ($user->clearance_level < $document->fileRecord->clearance_level) {
                return false;
            }

            // Allowed if user currently has custody
            if ($document->fileRecord->current_owner_id === $user->id) {
                return true;
            }

            // Department Heads can view files currently housed in their department
            if ($user->hasRole('Department Head') && $document->fileRecord->current_department_id === $user->department_id) {
                return true;
            }
        }

        // Original uploader always has access to their own document uploads
        return $document->uploaded_by === $user->id;
    }

    /**
     * Determine whether the user can create models (Upload).
     */
    public function create(User $user): bool
    {
        // Only active if the module is turned on
        return config('digital_module.enabled', true);
    }

    /**
     * Determine whether the user can update the model (Metadata Edit or Upload Version).
     */
    public function update(User $user, Document $document): bool
    {
        if (!config('digital_module.enabled', true)) {
            return false;
        }

        // Can update if they are the original uploader
        if ($document->uploaded_by === $user->id) {
            return true;
        }

        // Or if they currently hold custody of the physical file it's attached to
        if ($document->fileRecord && $document->fileRecord->current_owner_id === $user->id) {
            return true;
        }

        return $user->hasRole('System Administrator');
    }

    /**
     * Determine whether the user can delete the model (Soft Delete).
     */
    public function delete(User $user, Document $document): bool
    {
        if (!config('digital_module.enabled', true)) {
            return false;
        }

        // Department heads or sysadmins only
        return $user->hasRole('Department Head') || $user->hasRole('System Administrator');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Document $document): bool
    {
        return clone $user->hasRole('System Administrator'); // Extreme restriction
    }
}
