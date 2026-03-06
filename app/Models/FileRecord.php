<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FileRecord extends Model
{
    protected $guarded = [];

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName()
    {
        return 'uuid';
    }

    public function status()
    {
        return $this->belongsTo(Status::class, 'status_id');
    }

    public function movements()
    {
        return $this->hasMany(FileMovement::class, 'file_id');
    }

    public function originatingDepartment()
    {
        return $this->belongsTo(Department::class, 'originating_department_id');
    }

    public function currentDepartment()
    {
        return $this->belongsTo(Department::class, 'current_department_id');
    }

    public function currentOwner()
    {
        return $this->belongsTo(User::class, 'current_owner_id');
    }

    /**
     * Optional Phase 12 Digital Document Attachments
     */
    public function documents()
    {
        return $this->hasMany(Document::class, 'file_id')->latest();
    }
}
