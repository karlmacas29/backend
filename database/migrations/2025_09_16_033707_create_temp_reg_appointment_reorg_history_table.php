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
        Schema::dropIfExists('temp_reg_appointment_reorg_history');
        Schema::create('temp_reg_appointment_reorg_history', function (Blueprint $table) {
            $table->decimal('ID', 18, 0)->primary();
            $table->string('ControlNo', 10)->nullable();
            $table->string('DesigCode', 10)->nullable();
            $table->string('NewDesignation', 200)->nullable();
            $table->string('Designation', 200)->nullable();
            $table->decimal('SG', 18, 0)->nullable();
            $table->decimal('Step', 18, 0)->nullable();
            $table->string('Status', 50)->nullable();
            $table->string('OffCode', 10)->nullable();
            $table->string('NewOffice', 200)->nullable();
            $table->string('Office', 200)->nullable();
            $table->float('MRate')->nullable();
            $table->decimal('ItemNo', 18, 0)->nullable();
            $table->decimal('Pages', 18, 0)->nullable();
            $table->string('DivCode', 10)->nullable();
            $table->string('SecCode', 10)->nullable();
            $table->boolean('Official')->default(false);
            $table->string('Renew', 50)->nullable();
            $table->decimal('StructureID', 18, 0)->nullable();
            $table->string('groupcode', 10)->nullable();
            $table->string('group', 100)->nullable();
            $table->string('unitcode', 10)->nullable();
            $table->string('unit', 100)->nullable();
            $table->date('sepdate')->nullable();
            $table->string('sepcause', 50)->nullable();
            $table->string('vicename', 100)->nullable();
            $table->string('vicecause', 50)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('temp_reg_appointment_reorg_history');
    }
};
