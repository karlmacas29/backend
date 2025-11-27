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
        // Schema::create('temp_reg_appointment_reorg_confirmation', function (Blueprint $table) {
        //     $table->id(); // Laravel auto-increment ID
        //     $table->string('tempId')->nullable();
        //     $table->string('ControlNo')->nullable();
        //     $table->string('DesigCode')->nullable();
        //     $table->string('NewDesignation')->nullable();
        //     $table->string('Designation')->nullable();
        //     $table->string('SG')->nullable();
        //     $table->string('Step')->nullable();
        //     $table->string('Status')->nullable();
        //     $table->string('OffCode')->nullable();
        //     $table->string('NewOffice')->nullable();
        //     $table->string('Office')->nullable();
        //     $table->integer('MRate')->nullable();
        //     $table->string('ItemNo')->nullable();
        //     $table->string('Pages')->nullable();
        //     $table->string('DivCode')->nullable();
        //     $table->string('SecCode')->nullable();
        //     $table->string('Official')->nullable();
        //     $table->string('Renew')->nullable();
        //     $table->integer('StructureID')->nullable();
        //     $table->string('groupcode')->nullable();
        //     $table->string('group')->nullable();
        //     $table->string('unitcode')->nullable();
        //     $table->string('unit')->nullable();
        //     $table->date('sepdate')->nullable();
        //     $table->string('sepcause')->nullable();
        //     $table->string('vicename')->nullable();
        //     $table->string('vicecause')->nullable();

        //     $table->date('approved_date')->nullable();
        //     $table->string('approved_by')->nullable();
        //     $table->tinyInteger('approved_status')->default(2);
        //     $table->timestamps(); // created_at & updated_at
        // });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Schema::dropIfExists('temp_reg_appointment_reorg_confirmation');
    }
};
