<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // public function up(): void
    // {
    //     Schema::table('draft_table', function (Blueprint $table) {
    //         // Step 1: Drop the foreign key constraint
    //         $table->dropForeign(['nPersonalInfo_id']);
    //     });

    //     Schema::table('draft_table', function (Blueprint $table) {
    //         // Step 2: Alter the column to be nullable
    //         $table->unsignedBigInteger('nPersonalInfo_id')->nullable()->change();
    //     });

    //     Schema::table('draft_table', function (Blueprint $table) {
    //         // Step 3: Re-add the foreign key constraint
    //         $table->foreign('nPersonalInfo_id')
    //             ->references('id')
    //             ->on('nPersonalInfo')
    //             ->onDelete('cascade');
    //     });
    // }

    // public function down(): void
    // {
    //     Schema::table('draft_table', function (Blueprint $table) {
    //         $table->dropForeign(['nPersonalInfo_id']);
    //     });

    //     Schema::table('draft_table', function (Blueprint $table) {
    //         $table->unsignedBigInteger('nPersonalInfo_id')->nullable(false)->change();
    //     });

    //     Schema::table('draft_table', function (Blueprint $table) {
    //         $table->foreign('nPersonalInfo_id')
    //             ->references('id')
    //             ->on('nPersonalInfo')
    //             ->onDelete('cascade');
    //     });
    // }
};
