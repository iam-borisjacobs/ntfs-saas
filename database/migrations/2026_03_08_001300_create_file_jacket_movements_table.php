<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // jacket_movements table
        Schema::create('file_jacket_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jacket_id')->constrained('file_jackets')->cascadeOnDelete();
            $table->foreignId('from_department_id')->constrained('departments');
            $table->foreignId('from_user_id')->constrained('users');
            $table->foreignId('to_department_id')->constrained('departments');
            $table->foreignId('to_user_id')->nullable()->constrained('users');
            $table->timestamp('dispatched_at');
            $table->timestamp('received_at')->nullable();
            $table->string('status', 20)->default('PENDING_RECEIPT'); // PENDING_RECEIPT | RECEIVED
            $table->foreignId('dispatched_by')->constrained('users');
            $table->foreignId('received_by')->nullable()->constrained('users');
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->index(['jacket_id', 'status']);
            $table->index('to_department_id');
        });

        // Add location tracking to file_jackets
        Schema::table('file_jackets', function (Blueprint $table) {
            $table->foreignId('current_department_id')->nullable()->after('department_id')->constrained('departments');
            $table->foreignId('current_holder_user_id')->nullable()->after('current_department_id')->constrained('users');
        });

        // Backfill: set current_department_id = department_id for existing jackets
        DB::table('file_jackets')
            ->whereNull('current_department_id')
            ->update(['current_department_id' => DB::raw('department_id')]);
    }

    public function down(): void
    {
        Schema::dropIfExists('file_jacket_movements');
        Schema::table('file_jackets', function (Blueprint $table) {
            $table->dropForeign(['current_department_id']);
            $table->dropForeign(['current_holder_user_id']);
            $table->dropColumn(['current_department_id', 'current_holder_user_id']);
        });
    }
};
