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
        Schema::table('nWorkExperience', function (Blueprint $table) {
            $table->string('government_service')->nullable()->default(null)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('nWorkExperience', function (Blueprint $table) {
            $table->string('government_service')->nullable()->default('no')->change();
        });
    }
};
