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
        Schema::create('nNonAcademicDistinction', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->string('control_no', 50); // Control number to associate with the user
            $table->string('distinction', 255); // Non-academic distinction or recognition
            $table->timestamps(); // Created at and updated at timestamps
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nNonAcademicDistinction');
    }
};
