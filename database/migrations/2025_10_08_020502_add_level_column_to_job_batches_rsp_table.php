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
            //
            $table->string('tblStructureDetails_ID')->nullable()->after('post_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('job_batches_rsp', function (Blueprint $table) {
            //
            $table->dropColumn('tblStructureDetails_ID');
        });
    }
};
