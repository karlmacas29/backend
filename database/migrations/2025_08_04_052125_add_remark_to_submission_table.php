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
            $table->string('education_remark')->nullable()->after('job_batches_rsp_id');
            $table->string('experience_remark')->nullable()->after('education_remark');
            $table->string('training_remark')->nullable()->after('experience_remark');
            $table->string('eligibility_remark')->nullable()->after('training');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('submission', function (Blueprint $table) {
            $table->dropColumn('education_remark');
            $table->dropColumn('experience_remark');
            $table->dropColumn('training_remark');
            $table->dropColumn('eligibility_remark');
        });
    }
};
