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
        Schema::table('c_performance', function (Blueprint $table) {
            $table->string('Title')->nullable()->after('Rate'); // You can change the position if needed
        });
    }

    public function down(): void
    {
        Schema::table('c_performance', function (Blueprint $table) {
            $table->dropColumn('Title');
        });
    }
};
