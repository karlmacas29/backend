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
        Schema::create('nCivilService', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->string('control_no', 50); // Control number to associate with the user
            $table->string('eligibility', 255); // Eligibility title
            $table->decimal('rating', 5, 2)->nullable(); // Rating (if applicable)
            $table->date('date_of_examination')->nullable(); // Date of examination
            $table->string('place_of_examination', 255)->nullable(); // Place of examination
            $table->string('license_number', 50)->nullable(); // License number
            $table->date('date_of_validity')->nullable(); // Date of validity
            $table->timestamps(); // Created at and updated at timestamps
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nCivilService');
    }
};
