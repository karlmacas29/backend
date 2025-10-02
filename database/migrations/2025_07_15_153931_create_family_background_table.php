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
        Schema::dropIfExists('nFamily');
        Schema::create('nFamily', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->foreignId('nPersonalInfo_id')->constrained('nPersonalInfo')->onDelete('cascade');
            $table->string('spouse_name')->nullable(); // Spouse's surname
            $table->string('spouse_firstname')->nullable(); // Spouse's firstname
            $table->string('spouse_middlename')->nullable(); // Spouse's middlename
            $table->string('spouse_extension')->nullable(); // Spouse's name extension
            $table->string('spouse_occupation')->nullable(); // Spouse's occupation
            $table->string('spouse_employer')->nullable(); // Spouse's employer/business name
            $table->string('spouse_employer_address')->nullable(); // Spouse's business address
            $table->string('spouse_employer_telephone')->nullable(); // Spouse's employer telephone number
            $table->string('father_name')->nullable(); // Father's surname
            $table->string('father_firstname')->nullable(); // Father's firstname
            $table->string('father_middlename')->nullable(); // Father's middlename
            $table->string('father_extension')->nullable(); // Father's name extension
            $table->string('mother_name')->nullable(); // Mother's maiden surname
            $table->string('mother_firstname')->nullable(); // Mother's firstname
            $table->string('mother_middlename')->nullable(); // Mother's maiden middlename
            $table->string('mother_maidenname')->nullable(); // Mother's maiden surname
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
