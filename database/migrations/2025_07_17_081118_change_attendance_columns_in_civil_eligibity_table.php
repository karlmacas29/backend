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
        Schema::table('nCivilServiceEligibity', function (Blueprint $table) {
            $table->decimal('rating',8,2)->nullable()->change(); // revert to decimal
            $table->date('date_of_examination')->nullable()->change(); // revert to date
            $table->date('date_of_validity')->nullable()->change(); // revert to date
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('nCivilServiceEligibity', function (Blueprint $table) {
            $table->decimal('rating',14 ,12)->nullable()->change(); // revert to decimal
            $table->string('date_of_examination')->nullable()->change(); // was date
            $table->string('date_of_validity')->nullable()->change(); // was date
        });
    }
};
