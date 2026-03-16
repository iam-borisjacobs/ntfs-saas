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
        Schema::table('file_records', function (Blueprint $table) {
            $table->foreignId('reference_file_id')->nullable()->after('originating_department_id')->constrained('file_records')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('file_records', function (Blueprint $table) {
            //
        });
    }
};
