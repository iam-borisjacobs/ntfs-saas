<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemTerminology extends Model
{
    protected $fillable = [
        'key',
        'value',
        'default_value',
        'description',
        'group_name'
    ];

    /**
     * Retrieve a highly-cached terminology value by key.
     */
    public static function getTerm(string $key, string $default = null): string
    {
        // Cache the entire terminology array heavily to prevent thousands of DB queries per request
        $terminologies = \Illuminate\Support\Facades\Cache::rememberForever('system_terminologies', function () {
            // Failsafe in case table is completely dropped during migrations
            try {
                return self::pluck('value', 'key')->toArray();
            } catch (\Exception $e) {
                return [];
            }
        });

        if (array_key_exists($key, $terminologies)) {
            return $terminologies[$key];
        }

        // Fallback to the requested default, or a title-cased version of the key
        return $default ?? ucwords(str_replace('_', ' ', $key));
    }

    /**
     * Clear the cache automatically when values update
     */
    protected static function booted()
    {
        static::saved(function () {
            \Illuminate\Support\Facades\Cache::forget('system_terminologies');
        });

        static::deleted(function () {
            \Illuminate\Support\Facades\Cache::forget('system_terminologies');
        });
    }
}
