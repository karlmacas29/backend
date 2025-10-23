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
        Schema::create('posting_date', function (Blueprint $table) {
            $table->id();
            $table->string('ControlNo')->nullable();
            $table->date('post_date')->nullable();
            $table->date('end_date')->nullable();
            $table->foreignId('job_batches_rsp_id')->nullable()->constrained('job_batches_rsp')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posting_date');
    }
};
