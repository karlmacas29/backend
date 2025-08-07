<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Step 1: Update existing NULL values
        DB::table('criteria_rating')->whereNull('status')->update(['status' => 'not created']);

        // Step 2: Alter column to NOT NULL with default
        Schema::table('criteria_rating', function (Blueprint $table) {
            $table->string('status')->default('not created')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('criteria_rating', function (Blueprint $table) {
            $table->string('status')->nullable()->default(null)->change();
        });
    }
};
