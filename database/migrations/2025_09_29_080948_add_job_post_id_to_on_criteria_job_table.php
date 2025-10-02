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
            $table->foreignId('job_batches_rsp_id')->nullable()->after('id')->constrained('job_batches_rsp')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('on_criteria_job', function (Blueprint $table) {
            // First drop the foreign key constraint
            $table->dropForeign(['job_batches_rsp_id']);
            // Then drop the column itself
            $table->dropColumn('job_batches_rsp_id');
        });
    }
};
