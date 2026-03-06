<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        \Illuminate\Support\Facades\DB::unprepared("
            CREATE INDEX idx_department_inbox
            ON file_movements (to_department_id, acknowledgment_status)
            WHERE to_user_id IS NULL AND acknowledgment_status = 'PENDING';
        ");
    }

    public function down(): void
    {
        \Illuminate\Support\Facades\DB::unprepared("
            DROP INDEX IF EXISTS idx_department_inbox;
        ");
    }
};
