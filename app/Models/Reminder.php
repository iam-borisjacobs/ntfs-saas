<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Reminder extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id',
        'reminder_date',
        'title',
        'description',
        'is_completed',
    ];

    protected $casts = [
        'reminder_date' => 'date',
        'is_completed' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
