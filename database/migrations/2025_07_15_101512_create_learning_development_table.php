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
        Schema::dropIfExists('nTrainings');
        Schema::create('nTrainings', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->foreignId('nPersonalInfo_id')->constrained('nPersonalInfo')->onDelete('cascade');
            $table->string('training_title')->nullable(); // Title of the training
            $table->date('inclusive_date_from')->nullable(); // Inclusive date from
            $table->date('inclusive_date_to')->nullable(); // Inclusive date to
            $table->integer('number_of_hours')->nullable(); // Number of hours
            $table->string('type')->nullable(); // Type of L&D
            $table->string('conducted_by')->nullable(); // Conducted/Sponsored by
            $table->timestamps(); // Created at and updated at timestamps
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nTrainings');
    }
};
