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
        Schema::table('nPersonalInfo', function (Blueprint $table) {
            $table->string('excel_file')->nullable()->after('permanent_zip'); // or place it after any column you want
        });
    }

    public function down(): void
    {
        Schema::table('nPersonalInfo', function (Blueprint $table) {
            $table->dropColumn('excel_file');
        });
    }
};
