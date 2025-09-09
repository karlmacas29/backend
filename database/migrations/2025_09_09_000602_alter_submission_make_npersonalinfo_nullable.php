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
        Schema::table('submission', function (Blueprint $table) {
            // 1. Drop the existing foreign key
            $table->dropForeign(['nPersonalInfo_id']);

            // 2. Make column nullable
            $table->unsignedBigInteger('nPersonalInfo_id')->nullable()->change();

            // 3. Re-add foreign key constraint
            $table->foreign('nPersonalInfo_id')
                ->references('id')
                ->on('nPersonalInfo')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('submission', function (Blueprint $table) {
            $table->dropForeign(['nPersonalInfo_id']);

            $table->unsignedBigInteger('nPersonalInfo_id')->nullable(false)->change();

            $table->foreign('nPersonalInfo_id')
                ->references('id')
                ->on('nPersonalInfo')
                ->onDelete('cascade');
        });
    }
};
