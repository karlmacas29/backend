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
        Schema::create('nFamily', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->string('control_no', 50); // Control number
            $table->string('spouse_name', 100)->nullable(); // Spouse's surname
            $table->string('spouse_firstname', 100)->nullable(); // Spouse's firstname
            $table->string('spouse_middlename', 100)->nullable(); // Spouse's middlename
            $table->string('spouse_occupation', 100)->nullable(); // Spouse's occupation
            $table->string('spouse_employer', 255)->nullable(); // Spouse's employer/business name
            $table->string('spouse_employer_address', 255)->nullable(); // Spouse's business address
            $table->string('spouse_employer_telephone', 20)->nullable(); // Spouse's employer telephone number
            $table->string('father_name', 100)->nullable(); // Father's surname
            $table->string('father_firstname', 100)->nullable(); // Father's firstname
            $table->string('father_middlename', 100)->nullable(); // Father's middlename
            $table->string('mother_name', 100)->nullable(); // Mother's maiden surname
            $table->string('mother_firstname', 100)->nullable(); // Mother's firstname
            $table->string('mother_middlename', 100)->nullable(); // Mother's maiden middlename
            $table->timestamps(); // Created at and updated at timestamps
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nFamily');
    }
};
