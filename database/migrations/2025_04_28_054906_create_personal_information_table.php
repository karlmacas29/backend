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
        Schema::create('nPersonalInfo', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->string('control_no', 50)->unique(); // Unique control number
            $table->string('lastname', 100); // Last name
            $table->string('firstname', 100); // First name
            $table->string('middlename', 100)->nullable(); // Middle name (nullable)
            $table->string('sex', 10); // Sex (Male/Female)
            $table->string('civil_status', 20); // Civil status
            $table->string('tin_no', 50)->nullable(); // TIN number
            $table->string('gsis_no', 50)->nullable(); // GSIS number
            $table->string('pagibig_no', 50)->nullable(); // PAGIBIG number
            $table->string('sss_no', 50)->nullable(); // SSS number
            $table->string('philhealth_no', 50)->nullable(); // PhilHealth number
            $table->string('citizenship', 50); // Citizenship
            $table->string('citizenship_status', 50)->nullable(); // Citizenship status
            $table->string('religion', 100)->nullable(); // Religion
            $table->string('residential_house', 255)->nullable(); // Residential house/block/lot
            $table->string('residential_street', 255)->nullable(); // Residential street
            $table->string('residential_subdivision', 255)->nullable(); // Residential subdivision
            $table->string('residential_barangay', 100)->nullable(); // Residential barangay
            $table->string('residential_city', 100)->nullable(); // Residential city
            $table->string('residential_province', 100)->nullable(); // Residential province
            $table->string('residential_region', 100)->nullable(); // Residential region
            $table->string('residential_zip', 10)->nullable(); // Residential ZIP code
            $table->string('permanent_region', 100)->nullable(); // Permanent region
            $table->string('permanent_house', 255)->nullable(); // Permanent house/block/lot
            $table->string('permanent_street', 255)->nullable(); // Permanent street
            $table->string('permanent_subdivision', 255)->nullable(); // Permanent subdivision
            $table->string('permanent_barangay', 100)->nullable(); // Permanent barangay
            $table->string('permanent_city', 100)->nullable(); // Permanent city
            $table->string('permanent_province', 100)->nullable(); // Permanent province
            $table->string('permanent_zip', 10)->nullable(); // Permanent ZIP code
            $table->string('gender', 50)->nullable(); // Gender
            $table->decimal('height', 5, 2)->nullable(); // Height
            $table->decimal('weight', 5, 2)->nullable(); // Weight
            $table->string('blood_type', 5)->nullable(); // Blood type
            $table->string('telephone_number', 20)->nullable(); // Telephone number
            $table->date('date_of_birth'); // Date of birth
            $table->string('place_of_birth', 255)->nullable(); // Place of birth
            $table->string('email_address', 255)->nullable(); // Email address
            $table->string('cellphone_number', 20)->nullable(); // Cellphone number
            $table->timestamps(); // Created at and updated at timestamps
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nPersonalInfo'); // Drop the table if it exists
    }
};
