<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    public const UPDATED_AT = null;

    protected $guarded = [];

    protected static function boot()
    {
        parent::boot();

        static::updating(function ($model) {
            throw new \Exception('Audit Logs are immutable and cannot be updated.');
        });

        static::deleting(function ($model) {
            throw new \Exception('Audit Logs are immutable and cannot be deleted.');
        });
    }
}
