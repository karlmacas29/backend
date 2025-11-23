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
        Schema::table('c_behavioral_bei', function (Blueprint $table) {
            //
            $table->integer('percentage')->after('description')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('c_behavioral_bei', function (Blueprint $table) {
            //
            $table->dropColumn('percentage');
        });
    }
};
