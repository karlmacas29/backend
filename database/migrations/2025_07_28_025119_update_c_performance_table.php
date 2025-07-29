<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('c_performance', function (Blueprint $table) {
            // Drop existing columns
            $table->dropColumn(['Outstanding_rating', 'Very_satisfactory','Below_rating','Title']);

            // Add new JSON column
            $table->json('description')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('c_performance', function (Blueprint $table) {
            // Rollback: Add removed columns again
            $table->string('Outstanding_rating')->nullable();
            $table->string('Very_satisfactory')->nullable();
            $table->string('Below_rating')->nullable();
            $table->string('Title')->nullable();
            // Drop the new column
            $table->dropColumn('description');
        });
    }
};
