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
        if (\Illuminate\Support\Facades\DB::connection()->getDriverName() === 'pgsql') {
            \Illuminate\Support\Facades\DB::unprepared("
                CREATE TABLE audit_logs (
                    id BIGSERIAL,
                    agency_id BIGINT NOT NULL DEFAULT 1,
                    action_type VARCHAR(50) NOT NULL,
                    entity_type VARCHAR(50) NOT NULL,
                    entity_id BIGINT NOT NULL,
                    old_values JSONB,
                    new_values JSONB,
                    user_id BIGINT REFERENCES users(id),
                    ip_address INET,
                    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL,
                    PRIMARY KEY (id, created_at)
                ) PARTITION BY RANGE (created_at);

                CREATE TABLE audit_logs_2026 PARTITION OF audit_logs FOR VALUES FROM ('2026-01-01') TO ('2027-01-01');
                CREATE TABLE audit_logs_2027 PARTITION OF audit_logs FOR VALUES FROM ('2027-01-01') TO ('2028-01-01');

                CREATE INDEX idx_audit_entities ON audit_logs (entity_type, entity_id);
            ");
        } else {
            Schema::create('audit_logs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('agency_id')->default(1);
                $table->string('action_type', 50);
                $table->string('entity_type', 50);
                $table->unsignedBigInteger('entity_id');
                $table->json('old_values')->nullable();
                $table->json('new_values')->nullable();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->ipAddress('ip_address')->nullable();
                $table->timestamp('created_at')->useCurrent();

                $table->index(['entity_type', 'entity_id'], 'idx_audit_entities');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (\Illuminate\Support\Facades\DB::connection()->getDriverName() === 'pgsql') {
            \Illuminate\Support\Facades\DB::unprepared("
                DROP TABLE IF EXISTS audit_logs CASCADE;
            ");
        } else {
            Schema::dropIfExists('audit_logs');
        }
    }
};
