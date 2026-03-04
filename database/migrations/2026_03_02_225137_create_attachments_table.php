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
        Schema::create('attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('file_id')->constrained('file_records');
            $table->foreignId('uploaded_by')->constrained('users');
            $table->string('original_filename');
            $table->string('storage_path');
            $table->string('mime_type', 100);
            $table->bigInteger('file_size_bytes');
            $table->string('file_hash_sha256', 64);
            $table->string('virus_scan_status', 20)->default('PENDING');
            $table->timestamps();
        });

        \Illuminate\Support\Facades\DB::unprepared("
            CREATE OR REPLACE FUNCTION prevent_orphaned_attachments() RETURNS TRIGGER AS $$
            BEGIN
                IF EXISTS (SELECT 1 FROM file_records WHERE id = NEW.file_id AND status_id IN (
                    SELECT id FROM statuses WHERE name IN ('ARCHIVED', 'CLOSED')
                )) THEN
                    RAISE EXCEPTION 'Cannot add digital attachments to closed or archived files.';
                END IF;
                RETURN NEW;
            END;
            $$ LANGUAGE plpgsql;

            CREATE TRIGGER enforce_attachment_validity
            BEFORE INSERT ON attachments
            FOR EACH ROW EXECUTE FUNCTION prevent_orphaned_attachments();
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        \Illuminate\Support\Facades\DB::unprepared("
            DROP TRIGGER IF EXISTS enforce_attachment_validity ON attachments;
            DROP FUNCTION IF EXISTS prevent_orphaned_attachments();
        ");
        Schema::dropIfExists('attachments');
    }
};
