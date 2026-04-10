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
        Schema::create('system_terminologies', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique()->index();
            $table->string('value');
            $table->string('default_value')->nullable();
            $table->text('description')->nullable();
            $table->string('group_name')->default('general');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_terminologies');
    }
};
