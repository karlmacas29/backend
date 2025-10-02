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
        Schema::dropIfExists('job_batches_rsp');
        Schema::create('job_batches_rsp', function (Blueprint $table) {
            $table->id();
            $table->text('Office')->nullable();
            $table->text('Office2')->nullable();
            $table->text('Group')->nullable();
            $table->text('Division')->nullable();
            $table->text('Section')->nullable();
            $table->text('Unit')->nullable();
            $table->text('Position');
            $table->timestamp('post_date')->nullable();
            $table->integer('PositionID')->nullable();
            $table->boolean('isOpen')->default(true);
            $table->timestamps(); // includes created_at and updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_batches_rsp');
    }
};
