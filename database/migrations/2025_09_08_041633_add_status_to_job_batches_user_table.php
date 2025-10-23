<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    // public function up(): void
    // {
    //     Schema::table('job_batches_user', function (Blueprint $table) {
    //         $table->enum('status', ['pending', 'complete', 'Occupied', 'Unoccupied','assessed', 'not_assessed','rated','republished'])
    //             ->default('pending')
    //             ->after('job_batches_rsp_id'); // place it after this column
    //     });
    // }

    // /**
    //  * Reverse the migrations.
    //  */
    // public function down(): void
    // {
    //     Schema::table('job_batches_user', function (Blueprint $table) {
    //         $table->dropColumn('status');
    //     });
    // }
};
