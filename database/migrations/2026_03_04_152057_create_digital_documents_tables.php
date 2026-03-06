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
        Schema::create('documents', function (Blueprint $table) {
            $table->uuid('id')->primary();
            
            // Nullable because it might be attached to a specific file or float independently
            $table->foreignId('file_id')->nullable()->constrained('file_records')->nullOnDelete();
            
            // Optional: attach to a precise historical movement (e.g. "Memo attached during transfer")
            $table->foreignId('movement_id')->nullable()->constrained('file_movements')->nullOnDelete();
            
            $table->foreignId('uploaded_by')->constrained('users')->cascadeOnDelete();
            
            $table->string('document_type'); // Memo, Letter, Approval
            $table->string('file_name');
            $table->string('file_path');
            $table->unsignedBigInteger('file_size');
            $table->string('file_hash', 64); // SHA-256 is 64 chars
            $table->decimal('version_number', 5, 1)->default(1.0);
            
            $table->string('status')->default('ACTIVE'); // ACTIVE, ARCHIVED
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('document_versions', function (Blueprint $table) {
            $table->id();
            $table->uuid('document_id');
            $table->foreign('document_id')->references('id')->on('documents')->cascadeOnDelete();
            
            $table->decimal('version_number', 5, 1);
            $table->string('file_path');
            $table->foreignId('uploaded_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('download_logs', function (Blueprint $table) {
            $table->id();
            $table->uuid('document_id');
            $table->foreign('document_id')->references('id')->on('documents')->cascadeOnDelete();
            
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('ip_address', 45)->nullable();
            
            $table->timestamp('downloaded_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('download_logs');
        Schema::dropIfExists('document_versions');
        Schema::dropIfExists('documents');
    }
};
