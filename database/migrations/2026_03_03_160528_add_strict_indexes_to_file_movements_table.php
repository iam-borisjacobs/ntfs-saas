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
        Schema::table('file_movements', function (Blueprint $table) {
            $table->index(['file_id', 'dispatched_at'], 'idx_file_id_dispatched_at');
            $table->index(['to_user_id', 'acknowledgment_status'], 'idx_to_user_ack_status');
            $table->index(['from_user_id'], 'idx_from_user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('file_movements', function (Blueprint $table) {
            $table->dropIndex('idx_file_id_dispatched_at');
            $table->dropIndex('idx_to_user_ack_status');
            $table->dropIndex('idx_from_user_id');
        });
    }
};
