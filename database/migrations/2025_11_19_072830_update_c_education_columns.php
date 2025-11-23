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
        Schema::table('c_education', function (Blueprint $table) {

            // Change JSON description → string
            $table->string('description')->nullable()->change();

            // Rename Rate → weight
            $table->renameColumn('Rate', 'weight');
        });
    }

    public function down(): void
    {
        Schema::table('c_education', function (Blueprint $table) {

            // Reverse weight → Rate
            $table->renameColumn('weight', 'Rate');

            // Reverse string → json
            $table->json('description')->nullable()->change();
        });
    }
};
