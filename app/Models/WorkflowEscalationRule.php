<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkflowEscalationRule extends Model
{
    protected $guarded = [];

    protected $casts = [
        'notify_originator' => 'boolean',
        'notify_department_head' => 'boolean',
    ];

    public function status()
    {
        return $this->belongsTo(Status::class);
    }
}
