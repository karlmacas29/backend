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
        Schema::create('question_answer', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nPersonalInfo_id')->constrained('nPersonalInfo')->onDelete('cascade');
            $table->string('34_question')->nullable();
            $table->string('35_question')->nullable();
            $table->string('36_question')->nullable();
            $table->string('37_question')->nullable();
            $table->string('38_question')->nullable();
            $table->string('39_question')->nullable();
            $table->string('40_question')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('question_answer');
    }
};
