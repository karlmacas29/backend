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
            $table->string('education_score')->nullable()->after('job_batches_rsp_id');
            $table->decimal('experience_score')->nullable()->after('education_score');
            $table->decimal('training_score')->nullable()->after('experience_score');
            $table->decimal('performance_score')->nullable()->after('training_score');
            $table->decimal('behavioral_score')->nullable()->after('performance_score');
            $table->decimal('total_qs')->nullable()->after('behavioral_score');
            $table->decimal('grand_total')->nullable()->after('total_qs');
            $table->decimal('ranking')->nullable()->after('grand_total');
            $table->string('status')->default('pending')->after('ranking');
            $table->boolean('submitted')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('submission', function (Blueprint $table) {
            $table->dropColumn('education_score');
            $table->dropColumn('experience_score');
            $table->dropColumn('training_score');
            $table->dropColumn('performance_score');
            $table->dropColumn('behavioral_score');
            $table->dropColumn('total_qs');
            $table->dropColumn('grand_total');
            $table->dropColumn('ranking');
            $table->dropColumn('status');

        });
    }
};
