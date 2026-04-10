<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Ledger Immutability Trigger
        if (\Illuminate\Support\Facades\DB::connection()->getDriverName() === 'pgsql') {
            \Illuminate\Support\Facades\DB::unprepared("
            CREATE OR REPLACE FUNCTION enforce_movement_immutability()
            RETURNS TRIGGER AS $$
            BEGIN
                IF OLD.acknowledgment_status IN ('ACCEPTED', 'REJECTED') THEN
                    RAISE EXCEPTION 'File movement ledger is immutable once ACCEPTED or REJECTED. Row ID: %', OLD.id;
                END IF;
                RETURN NEW;
            END;
            $$ LANGUAGE plpgsql;

            CREATE TRIGGER file_movements_immutability_trigger
            BEFORE UPDATE ON file_movements
            FOR EACH ROW EXECUTE FUNCTION enforce_movement_immutability();
        ");
        }

        // 2. Terminal State Guard Trigger
        if (\Illuminate\Support\Facades\DB::connection()->getDriverName() === 'pgsql') {
            \Illuminate\Support\Facades\DB::unprepared("
            CREATE OR REPLACE FUNCTION prevent_movement_on_terminal_state()
            RETURNS TRIGGER AS $$
            DECLARE
                is_terminal_state BOOLEAN;
            BEGIN
                SELECT s.is_terminal INTO is_terminal_state 
                FROM file_records f 
                JOIN statuses s ON f.status_id = s.id 
                WHERE f.id = NEW.file_id;
                
                IF is_terminal_state THEN
                    RAISE EXCEPTION 'Cannot insert movement. File % is in a terminal state.', NEW.file_id;
                END IF;
                RETURN NEW;
            END;
            $$ LANGUAGE plpgsql;

            CREATE TRIGGER terminal_state_guard_trigger
            BEFORE INSERT ON file_movements
            FOR EACH ROW EXECUTE FUNCTION prevent_movement_on_terminal_state();
        ");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        \Illuminate\Support\Facades\DB::unprepared('
            DROP TRIGGER IF EXISTS file_movements_immutability_trigger ON file_movements;
            DROP FUNCTION IF EXISTS enforce_movement_immutability();
            
            DROP TRIGGER IF EXISTS terminal_state_guard_trigger ON file_movements;
            DROP FUNCTION IF EXISTS prevent_movement_on_terminal_state();
        ');
    }
};
