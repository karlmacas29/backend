<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration {
    public function up(): void
    {
        Schema::table('c_training', function (Blueprint $table) {
            // Drop existing columns
            $table->dropColumn(['Min_qualification', 'Title', 'Description']);

            // Add new JSON column
            $table->json('description')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('c_education', function (Blueprint $table) {
            // Rollback: Add removed columns again
            $table->string('Min_qualification')->nullable();
            $table->string('Title')->nullable();
            $table->string('Description')->nullable();
            // Drop the new column
            $table->dropColumn('description');
        });
    }
};
