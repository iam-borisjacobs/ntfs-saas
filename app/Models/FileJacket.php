<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FileJacket extends Model
{
    protected $guarded = [];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function files()
    {
        return $this->hasMany(FileRecord::class, 'file_jacket_id');
    }

    /**
     * Documents currently physically stored in this jacket.
     */
    public function currentFiles()
    {
        return $this->hasMany(FileRecord::class, 'current_file_jacket_id');
    }

    public function currentDepartment()
    {
        return $this->belongsTo(Department::class, 'current_department_id');
    }

    public function currentHolder()
    {
        return $this->belongsTo(User::class, 'current_holder_user_id');
    }

    public function movements()
    {
        return $this->hasMany(FileJacketMovement::class, 'jacket_id');
    }

    public function latestMovement()
    {
        return $this->hasOne(FileJacketMovement::class, 'jacket_id')->latestOfMany('dispatched_at');
    }

    public function hasPendingDispatch(): bool
    {
        return $this->movements()->where('status', 'PENDING_RECEIPT')->exists();
    }

    /**
     * Check if all files in the jacket are closed (terminal status).
     */
    public function allFilesClosed(): bool
    {
        if ($this->files()->count() === 0) {
            return true;
        }

        return $this->files()
            ->whereHas('status', fn($q) => $q->where('is_terminal', false))
            ->count() === 0;
    }
}
