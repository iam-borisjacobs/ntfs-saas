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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('type', 50); // e.g. FILE_DISPATCHED, SLA_BREACH
            $table->string('entity_type', 100)->nullable(); // e.g. App\Models\FileRecord
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->text('message');
            $table->string('severity', 20)->default('LOW'); // LOW, MEDIUM, HIGH, CRITICAL
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            // Composite Index for efficient "get unread for user" queries
            $table->index(['user_id', 'is_read', 'created_at'], 'idx_user_unread_notifs');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
