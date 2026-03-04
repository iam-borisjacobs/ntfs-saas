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
        Schema::create('status_transitions', function (Blueprint $table) {
            $table->foreignId('from_status_id')->constrained('statuses')->onDelete('cascade');
            $table->foreignId('to_status_id')->constrained('statuses')->onDelete('cascade');
            $table->boolean('requires_admin')->default(false);
            $table->primary(['from_status_id', 'to_status_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('status_transitions');
    }
};
