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
        Schema::create('nWorkExperience', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->string('control_no', 50); // Control number to associate with the user
            $table->date('work_date_from'); // Inclusive date from
            $table->date('work_date_to')->nullable(); // Inclusive date to
            $table->string('position_title', 255); // Position title
            $table->string('department', 255); // Department or company
            $table->decimal('monthly_salary', 10, 2)->nullable(); // Monthly salary
            $table->string('salary_grade', 50)->nullable(); // Salary grade
            $table->string('status_of_appointment', 255)->nullable(); // Status of appointment
            $table->string('government_service', 10)->default('no'); // Government service (yes/no)
            $table->timestamps(); // created at and updated at timestamps
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nWorkExperience');
    }
};
