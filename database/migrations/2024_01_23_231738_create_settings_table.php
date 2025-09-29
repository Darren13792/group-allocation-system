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
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->integer('min_group_size');
            $table->integer('max_group_size');
            $table->integer('max_groups_per_topic');
            $table->integer('preference_size');
            $table->integer('ideal_size')->nullable();
            $table->dateTime('deadline')->nullable();
            $table->enum('status', ['notstarted', 'started', 'ended', 'approved'])->default('notstarted');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
