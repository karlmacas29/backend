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
        Schema::table('users', function (Blueprint $table) {
            // Drop foreign key constraint first
            $table->dropForeign(['job_batches_rsp_id']);

            // Then drop the column
            $table->dropColumn('job_batches_rsp_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('job_batches_rsp_id')
                ->nullable()
                ->after('active')
                ->constrained('job_batches_rsp')
                ->nullOnDelete();
        });
    }
};
