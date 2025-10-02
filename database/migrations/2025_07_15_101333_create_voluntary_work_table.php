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
        Schema::dropIfExists('nVoluntaryWork');
        Schema::create('nVoluntaryWork', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->foreignId('nPersonalInfo_id')->constrained('nPersonalInfo')->onDelete('cascade');
            $table->string('organization_name')->nullable(); // Name and address of the organization
            $table->date('inclusive_date_from')->nullable(); // Inclusive date from
            $table->date('inclusive_date_to')->nullable(); // Inclusive date to
            $table->integer('number_of_hours')->nullable(); // Number of hours
            $table->string('position')->nullable(); // Position
            $table->timestamps(); // Created at and updated at timestamps
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nVoluntaryWork');
    }
};
