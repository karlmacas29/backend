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
        Schema::table('job_batches_rsp', function (Blueprint $table) {
            $table->string('status')->default('not started'); // Or use nullable if you prefer
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('job_batches_rsp', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
