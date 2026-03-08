<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Make alerted_by nullable to support system-generated alerts (NULL = system)
        Schema::table('movement_alerts', function (Blueprint $table) {
            $table->foreignId('alerted_by')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('movement_alerts', function (Blueprint $table) {
            $table->foreignId('alerted_by')->nullable(false)->change();
        });
    }
};
