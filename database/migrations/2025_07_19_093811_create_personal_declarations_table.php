<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('personal_declarations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nPersonalInfo_id')->constrained('nPersonalInfo')->onDelete('cascade');
            // Q34
            $table->string('a_third_degree_answer')->nullable();
            $table->string('b_fourth_degree_answer')->nullable();
            $table->string('34_if_yes')->nullable();

            // Q35a
            $table->string('a_found_guilty')->nullable();
            $table->string('guilty_yes')->nullable();
            $table->string('b_criminally_charged')->nullable();
            $table->string('case_date_filed')->nullable();
            $table->string('case_status')->nullable();

            // Q36
            $table->string('36_convited_answer')->nullable();
            $table->string('36_if_yes')->nullable();

            // Q37
            $table->string('37_service')->nullable();
            $table->string('37_if_yes')->nullable();

            // Q38
            $table->string('a_candidate')->nullable();
            $table->string('candidate_yes')->nullable();

            $table->string('b_resigned')->nullable();
            $table->string('resigned_yes')->nullable();

            // Q39
            $table->string('39_status')->nullable();
            $table->string('39_if_yes')->nullable();

            // Q40
            $table->string('a_indigenous')->nullable();
            $table->string('indigenous_yes')->nullable();
            $table->string('b_disability')->nullable();
            $table->string('disability_yes')->nullable();
            $table->string('c_solo')->nullable();
            $table->string('solo_parent_yes')->nullable();


            $table->timestamps();
        });
    }
};
