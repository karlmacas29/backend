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
        Schema::table('skill_non_academic', function (Blueprint $table) {
            $table->json('skill')->nullable()->change(); // Skill or hobby
            $table->json('non_academic')->nullable()->change(); // Non-academic distinction or recognition
            $table->json('organization')->nullable()->change(); // Name of the organization
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('skill_non_academic', function (Blueprint $table) {
            $table->string('skill')->nullable()->change(); // Skill or hobby
            $table->string('non_academic')->nullable()->change(); // Non-academic distinction or recognition
            $table->string('organization')->nullable()->change(); // Name of the organization

        });
    }
};
