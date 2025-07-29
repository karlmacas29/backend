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
        Schema::table('on_criteria_job', function (Blueprint $table) {
            $table->integer('ItemNo')->nullable()->after('PositionID'); // Add after PositionID if preferred
        });
    }

    public function down(): void
    {
        Schema::table('on_criteria_job', function (Blueprint $table) {
            $table->dropColumn('ItemNo');
        });
    }
};
