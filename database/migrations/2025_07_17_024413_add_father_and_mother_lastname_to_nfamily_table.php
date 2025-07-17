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
        Schema::table('nFamily', function (Blueprint $table) {
            $table->string('father_lastname')->nullable()->after('father_firstname');
            $table->string('mother_lastname')->nullable()->after('mother_firstname');
        });
    }

    public function down(): void
    {
        Schema::table('nFamily', function (Blueprint $table) {
            $table->dropColumn(['father_lastname', 'mother_lastname']);
        });
    }
};
