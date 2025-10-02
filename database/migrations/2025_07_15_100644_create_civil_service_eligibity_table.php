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
        Schema::dropIfExists('nCivilServiceEligibity');
        Schema::create('nCivilServiceEligibity', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->foreignId('nPersonalInfo_id')->constrained('nPersonalInfo')->onDelete('cascade');
            $table->string('eligibility')->nullable(); // Eligibility title
            $table->decimal('rating')->nullable(); // Rating (if applicable)
            $table->date('date_of_examination')->nullable(); // Date of examination
            $table->string('place_of_examination')->nullable(); // Place of examination
            $table->string('license_number')->nullable(); // License number
            $table->date('date_of_validity')->nullable(); // Date of validity
            $table->timestamps(); // Created at and updated at timestamps
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nCivilServiceEligibity');
    }
};
