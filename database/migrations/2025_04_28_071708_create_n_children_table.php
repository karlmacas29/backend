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
        Schema::create('nChildren', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->string('control_no', 50); // Control number
            $table->string('child_name', 255); // Name of the child
            $table->date('birth_date'); // Date of birth
            $table->string('status', 50)->default('PENDING'); // Status of the child record
            $table->timestamps(); // Created at and updated at timestamps
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nChildren');
    }
};
