<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add created_by to file_records
        Schema::table('file_records', function (Blueprint $table) {
            $table->foreignId('created_by')->nullable()->after('current_owner_id')->constrained('users');
        });

        // 2. Backfill created_by from current_owner_id for existing records
        DB::statement('UPDATE file_records SET created_by = current_owner_id WHERE created_by IS NULL');

        // 3. Add performance index
        DB::unprepared('CREATE INDEX idx_file_created_by ON file_records (created_by);');

        // 4. Create movement_alerts table
        Schema::create('movement_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('file_id')->constrained('file_records');
            $table->foreignId('movement_id')->constrained('file_movements');
            $table->foreignId('alerted_by')->constrained('users');
            $table->timestamp('alerted_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movement_alerts');
        DB::unprepared('DROP INDEX IF EXISTS idx_file_created_by;');
        Schema::table('file_records', function (Blueprint $table) {
            $table->dropColumn('created_by');
        });
    }
};
