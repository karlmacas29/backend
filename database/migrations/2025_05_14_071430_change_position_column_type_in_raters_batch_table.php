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
        Schema::table('raters_batch', function (Blueprint $table) {
            // Change column type from json to string
            $table->string('position')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('raters_batch', function (Blueprint $table) {
            // Change back to json if needed
            $table->json('position')->change();
        });
    }
};
