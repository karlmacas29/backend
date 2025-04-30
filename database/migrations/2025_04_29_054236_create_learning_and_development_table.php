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
        Schema::create('nTrainings', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->string('control_no', 50); // Control number to associate with the user
            $table->string('training_title', 255); // Title of the training
            $table->date('inclusive_date_from'); // Inclusive date from
            $table->date('inclusive_date_to')->nullable(); // Inclusive date to
            $table->integer('number_of_hours')->nullable(); // Number of hours
            $table->string('type', 255)->nullable(); // Type of L&D
            $table->string('conducted_by', 255)->nullable(); // Conducted/Sponsored by
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
