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
        Schema::table('nEducation', function (Blueprint $table) {
            $table->date('attendance_from')->nullable()->change();
            $table->date('attendance_to')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('nEducation', function (Blueprint $table) {
            $table->string('attendance_from')->nullable()->change();
            $table->string('attendance_to')->nullable()->change();

            //
        });
    }
};
