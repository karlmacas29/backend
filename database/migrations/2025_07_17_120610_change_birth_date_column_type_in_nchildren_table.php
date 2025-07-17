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
        Schema::table('nVoluntaryWork', function (Blueprint $table) {
            $table->date('work_date_from')->nullable()->change(); // was date
            $table->date('work_date_to')->nullable()->change(); // was date
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('nVoluntaryWork', function (Blueprint $table) {
            $table->string('work_date_from')->nullable()->change(); // was decimal
            $table->string('work_date_to')->nullable()->change(); // was date

        });
    }
};
