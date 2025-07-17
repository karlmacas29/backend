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
            $table->string('firstname')->nullable();; // Last name
            $table->string('lastname')->nullable();; // First name
            $table->string('middlename')->nullable(); // Middle name (nullable)
            $table->string('name_extension')->nullable(); // Name extension (JR., SR., etc.)
            $table->string('image_path')->nullable(); // or name it just 'image'
            $table->date('date_of_birth')->nullable(); // Cellphone number; // Date of birth
            $table->string('place_of_birth')->nullable(); // Place of birth
            $table->string('sex')->nullable(); // Cellphone number; // Sex (Male/Female)
            $table->string('civil_status')->nullable(); // Civil status
            $table->string('gender_preference')->nullable(); // Cellphone number; // Sex (Male/Female)
            $table->float('height')->nullable(); // Height
            $table->float('weight')->nullable(); // Weight
            $table->string('blood_type')->nullable(); // Blood type
            $table->string('telephone_number')->nullable(); // Telephone number
            $table->string('email_address')->nullable(); // Email address
            $table->string('cellphone_number')->nullable(); // Cellphone number
            $table->string('tin_no')->nullable(); // TIN number
            $table->string('gsis_no')->nullable(); // GSIS number
            $table->string('pagibig_no')->nullable(); // PAGIBIG number
            $table->string('sss_no')->nullable(); // SSS number
            $table->string('philhealth_no')->nullable(); // PhilHealth number
            $table->string('agency_employee_no')->nullable(); // PhilHealth number
            $table->string('citizenship')->nullable(); // Cellphone number; // Citizenship
            $table->string('citizenship_status')->nullable(); // Citizenship status
            $table->string('religion')->nullable(); // Religion
            $table->string('residential_house')->nullable(); // Residential house/block/lot
            $table->string('residential_street')->nullable(); // Residential street
            $table->string('residential_subdivision')->nullable(); // Residential subdivision
            $table->string('residential_barangay')->nullable(); // Residential barangay
            $table->string('residential_city')->nullable(); // Residential city
            $table->string('residential_province')->nullable(); // Residential province
            $table->string('residential_region')->nullable(); // Residential region
            $table->string('residential_zip')->nullable(); // Residential ZIP code
            $table->string('permanent_region')->nullable(); // Permanent region
            $table->string('permanent_house')->nullable(); // Permanent house/block/lot
            $table->string('permanent_street')->nullable(); // Permanent street
            $table->string('permanent_subdivision')->nullable(); // Permanent subdivision
            $table->string('permanent_barangay')->nullable(); // Permanent barangay
            $table->string('permanent_city')->nullable(); // Permanent city
            $table->string('permanent_province')->nullable(); // Permanent province
            $table->string('permanent_zip')->nullable(); // Permanent ZIP code
            $table->timestamps(); // Created at and updated at timestamps
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nPersonalInfo');
    }
};
