<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('submission', function (Blueprint $table) {

            $table->json('education_qualification')->nullable()->change();
            $table->json('experience_qualification')->nullable()->change();
            $table->json('training_qualification')->nullable()->change();
            $table->json('eligibility_qualification')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('submission', function (Blueprint $table) {

            $table->string('education_qualification')->nullable()->change();
            $table->string('experience_qualification')->nullable()->change();
            $table->string('training_qualification')->nullable()->change();
            $table->string('eligibility_qualification')->nullable()->change();
        });
    }
};
