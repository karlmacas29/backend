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
        Schema::create('on_criteria_job', function (Blueprint $table) {
            $table->id();
            $table->integer('PositionID')->nullable();
            $table->text('EduPercent')->nullable();
            $table->text('EliPercent')->nullable();
            $table->text('TrainPercent')->nullable();
            $table->text('ExperiencePercent')->nullable();
            $table->text('Education')->nullable();
            $table->text('Eligibility')->nullable();
            $table->text('Training')->nullable();
            $table->text('Experience')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('on_criteria_job');
    }
};
