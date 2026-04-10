<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Enable Trigram Extension for partial title matches (PostgreSQL only)
        if (\Illuminate\Support\Facades\DB::connection()->getDriverName() === 'pgsql') {
            \Illuminate\Support\Facades\DB::unprepared('CREATE EXTENSION IF NOT EXISTS pg_trgm;');
            \Illuminate\Support\Facades\DB::unprepared('CREATE INDEX idx_files_title_trgm ON file_records USING gin (title gin_trgm_ops);');
        } else {
            // Graceful fallback to standard indexing for MySQL/SQLite simple searches
            Schema::table('file_records', function (Blueprint $table) {
                $table->index('title', 'idx_files_title_basic');
            });
        }

        // Create specific indexes for movement timeline queries
        Schema::table('file_movements', function (Blueprint $table) {
            $table->index(['file_id', 'dispatched_at'], 'idx_movements_timeline');
        });

        // Create composite indexes for audit logs search
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->index(['entity_type', 'entity_id'], 'idx_audit_lookup');
            $table->index(['user_id', 'created_at'], 'idx_audit_temporal');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->dropIndex('idx_audit_temporal');
            $table->dropIndex('idx_audit_lookup');
        });

        Schema::table('file_movements', function (Blueprint $table) {
            $table->dropIndex('idx_movements_timeline');
        });

        if (\Illuminate\Support\Facades\DB::connection()->getDriverName() === 'pgsql') {
            \Illuminate\Support\Facades\DB::unprepared('DROP INDEX IF EXISTS idx_files_title_trgm;');
            // Avoid dropping extension as it might be used globally
        } else {
            Schema::table('file_records', function (Blueprint $table) {
                if (\Illuminate\Support\Facades\Schema::hasColumn('file_records', 'title')) {
                    $table->dropIndex('idx_files_title_basic');
                }
            });
        }
    }
};
