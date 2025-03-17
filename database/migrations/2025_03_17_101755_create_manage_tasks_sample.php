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
        Schema::create('manage_tasks_sample', function (Blueprint $table) {
            $table->id();
            $table->string('title',100 )->nullable();
            $table->text('description')->nullable();
            $table->string('category',50 )->nullable();
            $table->tinyInteger('status')->nullable();
            $table->tinyInteger('days')->nullable();
            $table->text('document')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('manage_tasks_sample');
    }
};
