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
        // Schema::dropIfExists('skill_non_academic');

        Schema::create('skill_non_academic', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nPersonalInfo_id')->constrained('nPersonalInfo')->onDelete('cascade');
            $table->json('skill')->nullable(); // Skill or hobby
            $table->json('non_academic')->nullable(); // Non-academic distinction or recognition
            $table->json('organization')->nullable(); // Name of the organization
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('skill_non_academic');
    }
};
