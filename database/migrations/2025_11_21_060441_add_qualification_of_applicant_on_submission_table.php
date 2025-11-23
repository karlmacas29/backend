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
            //

            $table->string('education_qualification')->after('ranking')->nullable();
            $table->string('experience_qualification')->after('education_qualification')->nullable();
            $table->string('training_qualification')->after('experience_qualification')->nullable();
            $table->string('eligibility_qualification')->after('training_qualification')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('submission', function (Blueprint $table) {
            //

            $table->dropColumn('education_qualification');
            $table->dropColumn('experience_qualification');
            $table->dropColumn('training_qualification');
            $table->dropColumn('eligibility_qualification');
        });
    }
};
