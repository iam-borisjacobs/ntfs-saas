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
        Schema::create('sequence_generators', function (Blueprint $table) {
            $table->integer('year')->primary();
            $table->bigInteger('current_value')->default(0);
        });

        Schema::create('file_records', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('agency_id')->default(1);
            $table->uuid('uuid')->unique();
            $table->string('file_reference_number', 50)->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->foreignId('originating_department_id')->constrained('departments');
            $table->foreignId('current_owner_id')->constrained('users');
            $table->foreignId('current_department_id')->constrained('departments');
            $table->foreignId('status_id')->constrained('statuses');
            $table->smallInteger('priority_level')->default(1);
            $table->smallInteger('confidentiality_level')->default(1);
            $table->timestamps();
            $table->timestamp('closed_at')->nullable();
        });

        \Illuminate\Support\Facades\DB::unprepared("
            ALTER TABLE file_records ADD CONSTRAINT chk_file_priority CHECK (priority_level IN (1, 2, 3));
            ALTER TABLE file_records ADD CONSTRAINT chk_file_confidentiality CHECK (confidentiality_level IN (1, 2, 3));

            CREATE OR REPLACE FUNCTION prevent_file_deletion() RETURNS TRIGGER AS $$
            BEGIN
                RAISE EXCEPTION 'Hard deletion of files is strictly prohibited. Update status to ARCHIVED.';
            END;
            $$ LANGUAGE plpgsql;

            CREATE TRIGGER enforce_file_retention
            BEFORE DELETE ON file_records
            FOR EACH ROW EXECUTE FUNCTION prevent_file_deletion();
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        \Illuminate\Support\Facades\DB::unprepared("
            DROP TRIGGER IF EXISTS enforce_file_retention ON file_records;
            DROP FUNCTION IF EXISTS prevent_file_deletion();
        ");
        Schema::dropIfExists('file_records');
        Schema::dropIfExists('sequence_generators');
    }
};
