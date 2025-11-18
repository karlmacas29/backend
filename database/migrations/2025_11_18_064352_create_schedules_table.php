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
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('submission_id')->constrained('submission')->onDelete('cascade');
            $table->string('batch_name')->nullable();
            $table->string('full_name')->nullable();
            $table->string('venue_interview')->nullable();
            $table->date('date_interview')->nullable();
            $table->time('time_interview')->nullable();
            // $table->string('status')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};
