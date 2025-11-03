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
        // Schema::dropIfExists('draft_table');
        Schema::create('draft_table', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nPersonalInfo_id')->nullable()->constrained('nPersonalInfo')->onDelete('cascade');
            $table->foreignId('job_batches_rsp_id')->nullable()->constrained('job_batches_rsp')->onDelete('cascade');
            $table->string('education_score')->nullable();
            $table->decimal('experience_score')->nullable();
            $table->decimal('training_score')->nullable();
            $table->decimal('performance_score')->nullable();
            $table->decimal('behavioral_score')->nullable();
            $table->decimal('total_qs')->nullable();
            $table->decimal('grand_total')->nullable();
            $table->integer('ranking')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('draft_table');
    }
};
