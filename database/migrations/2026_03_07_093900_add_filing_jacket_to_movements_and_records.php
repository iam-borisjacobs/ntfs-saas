<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add file_jacket_id to file_movements (receipt filing)
        Schema::table('file_movements', function (Blueprint $table) {
            $table->foreignId('file_jacket_id')->nullable()->constrained('file_jackets');
        });

        // 2. Add current_file_jacket_id to file_records (current physical location)
        Schema::table('file_records', function (Blueprint $table) {
            $table->foreignId('current_file_jacket_id')->nullable()->constrained('file_jackets');
        });
    }

    public function down(): void
    {
        Schema::table('file_movements', function (Blueprint $table) {
            $table->dropColumn('file_jacket_id');
        });
        Schema::table('file_records', function (Blueprint $table) {
            $table->dropColumn('current_file_jacket_id');
        });
    }
};
