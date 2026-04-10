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
        Schema::create('file_movements', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('agency_id')->default(1);
            $table->foreignId('file_id')->constrained('file_records');
            $table->foreignId('from_user_id')->constrained('users');
            $table->foreignId('from_department_id')->constrained('departments');
            $table->foreignId('to_user_id')->nullable()->constrained('users');
            $table->foreignId('to_department_id')->constrained('departments');
            $table->timestamp('dispatched_at')->useCurrent();
            $table->timestamp('received_at')->nullable();
            $table->string('acknowledgment_status', 20)->default('PENDING');
            $table->boolean('escalation_flag')->default(false);
            $table->text('remarks')->nullable();
        });

        if (\Illuminate\Support\Facades\DB::connection()->getDriverName() === 'pgsql') {
            \Illuminate\Support\Facades\DB::unprepared("
            ALTER TABLE file_movements ADD CONSTRAINT chk_ack_status CHECK (acknowledgment_status IN ('PENDING', 'ACCEPTED', 'REJECTED'));
            
            CREATE UNIQUE INDEX idx_single_pending_movement ON file_movements (file_id) WHERE acknowledgment_status = 'PENDING';
            CREATE INDEX idx_pending_receipts ON file_movements (to_user_id, acknowledgment_status) WHERE acknowledgment_status = 'PENDING';

            CREATE OR REPLACE FUNCTION enforce_movement_immutability() RETURNS TRIGGER AS $$
            BEGIN
                IF OLD.acknowledgment_status IN ('ACCEPTED', 'REJECTED') THEN
                    RAISE EXCEPTION 'Movement record is permanently locked and cannot be modified.';
                END IF;
                RETURN NEW;
            END;
            $$ LANGUAGE plpgsql;

            CREATE TRIGGER lock_completed_movements
            BEFORE UPDATE ON file_movements
            FOR EACH ROW EXECUTE FUNCTION enforce_movement_immutability();

            CREATE OR REPLACE FUNCTION prevent_inactive_assignment() RETURNS TRIGGER AS $$
            BEGIN
                IF NEW.to_user_id IS NOT NULL THEN
                    IF NOT EXISTS (SELECT 1 FROM users WHERE id = NEW.to_user_id AND is_active = TRUE) THEN
                        RAISE EXCEPTION 'Cannot assign custody to an inactive user.';
                    END IF;
                END IF;
                RETURN NEW;
            END;
            $$ LANGUAGE plpgsql;

            CREATE TRIGGER enforce_active_recipient
            BEFORE INSERT OR UPDATE ON file_movements
            FOR EACH ROW EXECUTE FUNCTION prevent_inactive_assignment();

            CREATE OR REPLACE FUNCTION prevent_close_with_pending_transit() RETURNS TRIGGER AS $$
            BEGIN
                IF NEW.status_id = (SELECT id FROM statuses WHERE name = 'CLOSED') THEN
                    IF EXISTS (SELECT 1 FROM file_movements WHERE file_id = NEW.id AND acknowledgment_status = 'PENDING') THEN
                        RAISE EXCEPTION 'Cannot close a file that has a pending movement transfer.';
                    END IF;
                END IF;
                RETURN NEW;
            END;
            $$ LANGUAGE plpgsql;

            CREATE TRIGGER enforce_valid_closure
            BEFORE UPDATE ON file_records
            FOR EACH ROW EXECUTE FUNCTION prevent_close_with_pending_transit();
        ");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        \Illuminate\Support\Facades\DB::unprepared("
            DROP TRIGGER IF EXISTS lock_completed_movements ON file_movements;
            DROP FUNCTION IF EXISTS enforce_movement_immutability();
            DROP TRIGGER IF EXISTS enforce_active_recipient ON file_movements;
            DROP FUNCTION IF EXISTS prevent_inactive_assignment();
            DROP TRIGGER IF EXISTS enforce_valid_closure ON file_records;
            DROP FUNCTION IF EXISTS prevent_close_with_pending_transit();
        ");
        Schema::dropIfExists('file_movements');
    }
};
