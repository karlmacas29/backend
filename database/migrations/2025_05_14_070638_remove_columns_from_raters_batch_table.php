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
        Schema::table('raters_batch', function (Blueprint $table) {
            $table->dropColumn(['assign_batch', 'office', 'pending', 'completed']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('raters_batch', function (Blueprint $table) {
            $table->date('assign_batch')->nullable(); // Assign Batch (Date)
            $table->string('office')->nullable(); // Office (varchar)
            $table->integer('pending')->nullable(); // Pending (int)
            $table->integer('completed')->nullable(); // Completed (int)
        });
    }
};
