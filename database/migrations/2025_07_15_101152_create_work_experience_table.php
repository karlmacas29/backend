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
            $table->foreignId('nPersonalInfo_id')->constrained('nPersonalInfo')->onDelete('cascade');
            $table->date('work_date_from')->nullable(); // Inclusive date from
            $table->date('work_date_to')->nullable(); // Inclusive date to
            $table->string('position_title')->nullable(); // Position title
            $table->string('department')->nullable(); // Department or company
            $table->decimal('monthly_salary')->nullable(); // Monthly salary
            $table->string('salary_grade')->nullable(); // Salary grade
            $table->string('status_of_appointment')->nullable(); // Status of appointment
            $table->string('government_service')->default('no')->nullable(); // Government service (yes/no)
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
