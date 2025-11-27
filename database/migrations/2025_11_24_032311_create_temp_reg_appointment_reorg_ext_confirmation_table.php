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
        // Schema::create('temp_reg_appointment_reorg_ext_confirmation', function (Blueprint $table) {
        //     $table->id();
        //     $table->integer('tempExtId');
        //     $table->string('ControlNo')->nullable();
        //     $table->string('PresAppro')->nullable();
        //     $table->string('PrevAppro')->nullable();
        //     $table->string('SalAuthorized')->nullable();
        //     $table->string('OtherComp')->nullable();
        //     $table->string('SupPosition')->nullable();
        //     $table->string('HSupPosition')->nullable();
        //     $table->string('Tool')->nullable();

        //     $table->integer('Contact1')->nullable();
        //     $table->integer('Contact2')->nullable();
        //     $table->integer('Contact3')->nullable();
        //     $table->integer('Contact4')->nullable();
        //     $table->integer('Contact5')->nullable();
        //     $table->integer('Contact6')->nullable();
        //     $table->string('ContactOthers')->nullable();

        //     $table->integer('Working1')->nullable();
        //     $table->integer('Working2')->nullable();
        //     $table->string('WorkingOthers')->nullable();

        //     $table->text('DescriptionSection')->nullable();
        //     $table->text('DescriptionFunction')->nullable();

        //     $table->text('StandardEduc')->nullable();
        //     $table->text('StandardExp')->nullable();
        //     $table->text('StandardTrain')->nullable();
        //     $table->text('StandardElig')->nullable();

        //     $table->string('Supervisor')->nullable();

        //     $table->integer('Core1')->nullable();
        //     $table->integer('Core2')->nullable();
        //     $table->integer('Core3')->nullable();
        //     $table->integer('Corelevel1')->nullable();
        //     $table->integer('Corelevel2')->nullable();
        //     $table->integer('Corelevel3')->nullable();
        //     $table->integer('Corelevel4')->nullable();

        //     $table->integer('Leader1')->nullable();
        //     $table->integer('Leader2')->nullable();
        //     $table->integer('Leader3')->nullable();
        //     $table->integer('Leader4')->nullable();
        //     $table->integer('leaderlevel1')->nullable();
        //     $table->integer('leaderlevel2')->nullable();
        //     $table->integer('leaderlevel3')->nullable();
        //     $table->integer('leaderlevel4')->nullable();

        //     $table->integer('structureid')->nullable();
        //     $table->date('approved_date')->nullable();
        //     $table->string('approved_by')->nullable();
        //     $table->tinyInteger('approved_status')->default(2);
        //     $table->timestamps();
        // });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Schema::dropIfExists('temp_reg_appointment_reorg_ext_confirmation');
    }
};
