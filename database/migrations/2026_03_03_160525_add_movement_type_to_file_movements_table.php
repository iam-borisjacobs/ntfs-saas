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
            $table->string('movement_type', 30)->default('DISPATCH');
        });

        // Add CHECK constraint
        if (\Illuminate\Support\Facades\DB::connection()->getDriverName() === 'pgsql') {
            \Illuminate\Support\Facades\DB::statement("
            ALTER TABLE file_movements
            ADD CONSTRAINT chk_movement_type 
            CHECK (movement_type IN ('CREATION', 'DISPATCH', 'RECEIVE', 'REJECT', 'RETURN', 'SYSTEM_ASSIGNMENT', 'ESCALATION_FLAG'))
        ");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (\Illuminate\Support\Facades\DB::connection()->getDriverName() === 'pgsql') {
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE file_movements DROP CONSTRAINT chk_movement_type");
        }
        
        Schema::table('file_movements', function (Blueprint $table) {
            $table->dropColumn('movement_type');
        });
    }
};
