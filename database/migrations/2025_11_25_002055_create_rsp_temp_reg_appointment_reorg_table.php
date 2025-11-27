<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    // /**
    //  * Run the migrations.
    //  */
    // public function up(): void
    // {
    //     Schema::create('rsp_temp_reg_appointment_reorg', function (Blueprint $table) {
    //         $table->string('ID')->nullable();
    //         $table->string('ControlNo')->nullable();
    //         $table->string('DesigCode')->nullable();
    //         $table->string('NewDesignation')->nullable();
    //         $table->string('Designation')->nullable();
    //         $table->string('SG')->nullable();
    //         $table->string('Step')->nullable();
    //         $table->string('Status')->nullable();
    //         $table->string('OffCode')->nullable();
    //         $table->string('NewOffice')->nullable();
    //         $table->string('Office')->nullable();
    //         $table->integer('MRate')->nullable();
    //         $table->string('ItemNo')->nullable();
    //         $table->string('Pages')->nullable();
    //         $table->string('DivCode')->nullable();
    //         $table->string('SecCode')->nullable();
    //         $table->string('Official')->nullable();
    //         $table->string('Renew')->nullable();
    //         $table->integer('StructureID')->nullable();
    //         $table->string('groupcode')->nullable();
    //         $table->string('group')->nullable();
    //         $table->string('unitcode')->nullable();
    //         $table->string('unit')->nullable();
    //         $table->date('sepdate')->nullable();
    //         $table->string('sepcause')->nullable();
    //         $table->string('vicename')->nullable();
    //         $table->string('vicecause')->nullable();
    //         $table->timestamps();
    //     });

    //     // COPY DATA FROM tempRegAppointmentReorg → rsp_temp_reg_appointment_reorg
    //     DB::statement("
    //     INSERT INTO rsp_temp_reg_appointment_reorg (
    //         ID, ControlNo, DesigCode, NewDesignation, Designation, SG, Step,
    //         Status, OffCode, NewOffice, Office, MRate, ItemNo, Pages, DivCode,
    //         SecCode, Official, Renew, StructureID, groupcode, [group], unitcode,
    //         unit, sepdate, sepcause, vicename, vicecause, created_at, updated_at
    //     )
    //     SELECT
    //         ID, ControlNo, DesigCode, NewDesignation, Designation, SG, Step,
    //         Status, OffCode, NewOffice, Office, MRate, ItemNo, Pages, DivCode,
    //         SecCode, Official, Renew, StructureID, groupcode, [group], unitcode,
    //         unit, sepdate, sepcause, vicename, vicecause, GETDATE(), GETDATE()
    //     FROM tempRegAppointmentReorg
    // ");
    // }


    // /**
    //  * Reverse the migrations.
    //  */
    // public function down(): void
    // {
    //     Schema::dropIfExists('rsp_temp_reg_appointment_reorg');
    // }
};
