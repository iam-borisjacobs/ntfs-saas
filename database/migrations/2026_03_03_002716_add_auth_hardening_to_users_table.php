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
        Schema::table('users', function (Blueprint $table) {
            $table->smallInteger('clearance_level')->default(1);
        });

        if (\Illuminate\Support\Facades\DB::connection()->getDriverName() === 'pgsql') {
            \Illuminate\Support\Facades\DB::unprepared("
            ALTER TABLE users ADD CONSTRAINT chk_user_clearance CHECK (clearance_level BETWEEN 1 AND 3);
            CREATE INDEX idx_users_active_clearance ON users (is_active, clearance_level);

            CREATE OR REPLACE FUNCTION audit_user_role_changes() RETURNS TRIGGER AS $$
            BEGIN
                IF TG_OP = 'INSERT' THEN
                    INSERT INTO audit_logs (action_type, entity_type, entity_id, new_values)
                    VALUES ('ROLE_ASSIGNED', 'model_has_roles', NEW.model_id, jsonb_build_object('role_id', NEW.role_id));
                    RETURN NEW;
                ELSIF TG_OP = 'DELETE' THEN
                    INSERT INTO audit_logs (action_type, entity_type, entity_id, old_values)
                    VALUES ('ROLE_REMOVED', 'model_has_roles', OLD.model_id, jsonb_build_object('role_id', OLD.role_id));
                    RETURN OLD;
                END IF;
                RETURN NULL;
            END;
            $$ LANGUAGE plpgsql;

            CREATE TRIGGER enforce_role_audit
            AFTER INSERT OR DELETE ON model_has_roles
            FOR EACH ROW EXECUTE FUNCTION audit_user_role_changes();
        ");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (\Illuminate\Support\Facades\DB::connection()->getDriverName() === 'pgsql') {
            \Illuminate\Support\Facades\DB::unprepared("
            DROP TRIGGER IF EXISTS enforce_role_audit ON model_has_roles;
            DROP FUNCTION IF EXISTS audit_user_role_changes();
            ALTER TABLE users DROP CONSTRAINT IF EXISTS chk_user_clearance;
            DROP INDEX IF EXISTS idx_users_active_clearance;
        ");
        }
        
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('clearance_level');
        });
    }
};
