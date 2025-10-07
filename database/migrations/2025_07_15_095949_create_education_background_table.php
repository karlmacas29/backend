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
        // Schema::dropIfExists('nEducation');
        Schema::create('nEducation', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->foreignId('nPersonalInfo_id')->constrained('nPersonalInfo')->onDelete('cascade');
            $table->string('school_name')->nullable(); // Name of the school
            $table->string('degree')->nullable(); // Degree or course
            $table->date('attendance_from')->nullable(); // Period of attendance (start date)
            $table->date('attendance_to')->nullable(); // Period of attendance (end date)
            $table->string('highest_units')->nullable(); // Highest level/units earned
            $table->string('year_graduated')->nullable(); // Year graduated
            $table->string('scholarship')->nullable(); // Scholarship or academic honors
            $table->string('level')->nullable(); // Level (Elementary, Secondary, College, etc.)
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
