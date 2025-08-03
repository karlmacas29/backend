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
        Schema::create('c_performance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('criteria_rating_id')->nullable()->constrained('criteria_rating')->onDelete('cascade');
            $table->string('Rate')->nullable();
            $table->json('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('c_performance');
    }
};
