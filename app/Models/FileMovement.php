<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FileMovement extends Model
{
    const CREATED_AT = 'dispatched_at';
    const UPDATED_AT = null;

    public $timestamps = false;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'dispatched_at' => 'datetime',
            'received_at' => 'datetime',
            'closed_at' => 'datetime',
            'movement_closed' => 'boolean',
            'escalation_flag' => 'boolean',
        ];
    }

    public function file()
    {
        return $this->belongsTo(FileRecord::class, 'file_id');
    }

    public function fromUser()
    {
        return $this->belongsTo(User::class, 'from_user_id');
    }

    public function toUser()
    {
        return $this->belongsTo(User::class, 'to_user_id');
    }

    public function fromDepartment()
    {
        return $this->belongsTo(Department::class, 'from_department_id');
    }

    public function toDepartment()
    {
        return $this->belongsTo(Department::class, 'to_department_id');
    }

    public function closedByUser()
    {
        return $this->belongsTo(User::class, 'closed_by');
    }
}
