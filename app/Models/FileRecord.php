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

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function jacket()
    {
        return $this->belongsTo(FileJacket::class, 'file_jacket_id');
    }

    public function currentFileJacket()
    {
        return $this->belongsTo(FileJacket::class, 'current_file_jacket_id');
    }

    /**
     * Get the document this file is replying to or referencing
     */
    public function reference()
    {
        return $this->belongsTo(FileRecord::class, 'reference_file_id');
    }

    /**
     * Get all documents that replied to or reference this file
     */
    public function referencedBy()
    {
        return $this->hasMany(FileRecord::class, 'reference_file_id');
    }

    /**
     * Optional Phase 12 Digital Document Attachments
     */
    public function documents()
    {
        return $this->hasMany(Document::class, 'file_id')->latest();
    }
}
