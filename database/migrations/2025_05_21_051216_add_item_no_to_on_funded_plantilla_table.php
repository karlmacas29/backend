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
        Schema::table('on_funded_plantilla', function (Blueprint $table) {
            $table->string('ItemNo')->nullable()->after('PositionID'); // Adds ItemNo after PositionID
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('on_funded_plantilla', function (Blueprint $table) {
            $table->dropColumn('ItemNo');
        });
    }
};
