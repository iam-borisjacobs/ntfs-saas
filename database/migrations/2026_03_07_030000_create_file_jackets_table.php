<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Create file_jackets table
        Schema::create('file_jackets', function (Blueprint $table) {
            $table->id();
            $table->string('jacket_code', 50)->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->foreignId('department_id')->constrained('departments');
            $table->foreignId('created_by')->constrained('users');
            $table->string('status', 20)->default('active');
            $table->timestamps();
        });

        // 2. Add file_jacket_id to file_records
        Schema::table('file_records', function (Blueprint $table) {
            $table->foreignId('file_jacket_id')->nullable()->constrained('file_jackets');
        });

        // 3. Performance indexes
        DB::unprepared('CREATE INDEX idx_jacket_department ON file_jackets (department_id);');
        DB::unprepared('CREATE INDEX idx_file_jacket_id ON file_records (file_jacket_id);');
    }

    public function down(): void
    {
        Schema::table('file_records', function (Blueprint $table) {
            $table->dropColumn('file_jacket_id');
        });
        DB::unprepared('DROP INDEX IF EXISTS idx_jacket_department;');
        DB::unprepared('DROP INDEX IF EXISTS idx_file_jacket_id;');
        Schema::dropIfExists('file_jackets');
    }
};
