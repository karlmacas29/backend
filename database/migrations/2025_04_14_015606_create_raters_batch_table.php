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
        Schema::create('raters_batch', function (Blueprint $table) {
            $table->id(); // Primary Key
            $table->string('raters'); // Raters (varchar)
            $table->date('assign_batch'); // Assign Batch (Date)
            $table->json('position'); // Position (json)
            $table->string('office'); // Office (varchar)
            $table->integer('pending'); // Pending (int)
            $table->integer('completed'); // Completed (int)
            $table->timestamps(); // Created at and Updated at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('raters_batch');
    }
};
