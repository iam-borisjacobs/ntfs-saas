<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add closure fields to file_movements
        Schema::table('file_movements', function (Blueprint $table) {
            $table->boolean('movement_closed')->default(false);
            $table->timestamp('closed_at')->nullable();
            $table->foreignId('closed_by')->nullable()->constrained('users');
            $table->text('closure_reason')->nullable();
        });

        // 2. Add performance index
        DB::unprepared("
            CREATE INDEX idx_movement_closed ON file_movements (movement_closed);
        ");

        // 3. Replace immutability trigger to allow closure-field updates on ACCEPTED movements
        if (\Illuminate\Support\Facades\DB::connection()->getDriverName() === 'pgsql') {
            DB::unprepared("
            CREATE OR REPLACE FUNCTION enforce_movement_immutability() RETURNS TRIGGER AS \$\$
            BEGIN
                IF OLD.acknowledgment_status IN ('ACCEPTED', 'REJECTED') THEN
                    -- Allow closure-field updates on ACCEPTED movements
                    IF OLD.acknowledgment_status = 'ACCEPTED'
                       AND NEW.acknowledgment_status = OLD.acknowledgment_status
                       AND NEW.movement_closed = TRUE
                       AND OLD.movement_closed = FALSE THEN
                        RETURN NEW;
                    END IF;
                    RAISE EXCEPTION 'Movement record is permanently locked and cannot be modified.';
                END IF;
                RETURN NEW;
            END;
            \$\$ LANGUAGE plpgsql;
        ");
        }

        // 4. Seed RECEIVED -> CLOSED status transition
        $receivedId = DB::table('statuses')->where('name', 'RECEIVED')->value('id');
        $closedId = DB::table('statuses')->where('name', 'CLOSED')->value('id');

        if ($receivedId && $closedId) {
            DB::table('status_transitions')->insertOrIgnore([
                'from_status_id' => $receivedId,
                'to_status_id' => $closedId,
            ]);
        }
    }

    public function down(): void
    {
        // Restore original trigger
        if (\Illuminate\Support\Facades\DB::connection()->getDriverName() === 'pgsql') {
            DB::unprepared("
            CREATE OR REPLACE FUNCTION enforce_movement_immutability() RETURNS TRIGGER AS \$\$
            BEGIN
                IF OLD.acknowledgment_status IN ('ACCEPTED', 'REJECTED') THEN
                    RAISE EXCEPTION 'Movement record is permanently locked and cannot be modified.';
                END IF;
                RETURN NEW;
            END;
            \$\$ LANGUAGE plpgsql;
        ");
        }

        // Remove transition
        $receivedId = DB::table('statuses')->where('name', 'RECEIVED')->value('id');
        $closedId = DB::table('statuses')->where('name', 'CLOSED')->value('id');
        if ($receivedId && $closedId) {
            DB::table('status_transitions')
                ->where('from_status_id', $receivedId)
                ->where('to_status_id', $closedId)
                ->delete();
        }

        Schema::table('file_movements', function (Blueprint $table) {
            $table->dropColumn(['movement_closed', 'closed_at', 'closed_by', 'closure_reason']);
        });
    }
};
