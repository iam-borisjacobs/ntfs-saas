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
        Schema::create('workflow_escalation_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('status_id')->constrained()->onDelete('cascade');
            $table->integer('max_duration_hours');
            $table->unsignedBigInteger('escalate_to_role_id');
            // Referencing Spatie roles table manually since there's no Role model by default
            $table->foreign('escalate_to_role_id')->references('id')->on('roles')->onDelete('cascade');
            
            $table->boolean('notify_originator')->default(false);
            $table->boolean('notify_department_head')->default(false);
            $table->timestamps();
            
            // Allow querying rules easily by status
            $table->index('status_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workflow_escalation_rules');
    }
};
