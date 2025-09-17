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
        Schema::create('submission', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nPersonalInfo_id')->constrained('nPersonalInfo')->onDelete('cascade');
            $table->string('ControlNo')->nullable()->after('nPersonalInfo_id');
            $table->foreignId('job_batches_rsp_id')->constrained('job_batches_rsp')->onDelete('cascade');
            $table->string('education_remark')->nullable();
            $table->string('experience_remark')->nullable();
            $table->string('training_remark')->nullable();
            $table->string('eligibility_remark')->nullable();
            $table->decimal('total_qs')->nullable();
            $table->decimal('grand_total')->nullable();
            $table->decimal('ranking')->nullable();
            $table->string('status')->default('pending')->after('ranking');
            $table->boolean('submitted')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('submission');
    }
};
