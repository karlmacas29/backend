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
            $table->string('PageNo')->nullable()->after('end_date');
            $table->string('ItemNo')->nullable()->after('PageNo');
            $table->string('SalaryGrade')->nullable()->after('ItemNo');
            $table->string('salaryMin')->nullable()->after('SalaryGrade');
            $table->string('salaryMax')->nullable()->after('salaryMin');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('job_batches_rsp', function (Blueprint $table) {
            $table->dropColumn(['PageNo', 'ItemNo', 'SalaryGrade', 'salaryMin', 'salaryMax']);
        });
    }
};
