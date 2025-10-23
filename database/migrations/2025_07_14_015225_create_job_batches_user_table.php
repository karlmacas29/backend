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

        //this is pivot table for job_batches_rsp and users
        // Schema::dropIfExists('job_batches_user');
        Schema::create('job_batches_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('job_batches_rsp_id')->constrained('job_batches_rsp')->onDelete('cascade');
            $table->enum('status', ['pending', 'complete', 'Occupied', 'Unoccupied', 'assessed', 'not started', 'rated', 'republished'])
                ->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_batches_user');
    }
};
