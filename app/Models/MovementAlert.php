<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MovementAlert extends Model
{
    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'alerted_at' => 'datetime',
    ];

    public function file()
    {
        return $this->belongsTo(FileRecord::class, 'file_id');
    }

    public function movement()
    {
        return $this->belongsTo(FileMovement::class, 'movement_id');
    }

    public function alertedBy()
    {
        return $this->belongsTo(User::class, 'alerted_by');
    }
}
