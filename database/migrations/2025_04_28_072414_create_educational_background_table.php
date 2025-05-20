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
        Schema::create('nEducation', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->string('control_no', 50); // Control number
            $table->string('school_name', 255); // Name of the school
            $table->string('degree', 255)->nullable(); // Degree or course
            $table->date('attendance_from')->nullable(); // Period of attendance (start date)
            $table->date('attendance_to')->nullable(); // Period of attendance (end date)
            $table->string('highest_units', 50)->nullable(); // Highest level/units earned
            $table->string('year_graduated', 10)->nullable(); // Year graduated
            $table->string('scholarship', 255)->nullable(); // Scholarship or academic honors
            $table->string('level', 50); // Level (Elementary, Secondary, College, etc.)
            $table->timestamps(); // Created at and updated at timestamps
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nEducation');
    }
};
