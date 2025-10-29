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
        // Schema::dropIfExists('personal_declarations');
        Schema::create('personal_declarations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nPersonalInfo_id')->constrained('nPersonalInfo')->onDelete('cascade');
            // Q34
            // $table->string('a_third_degree_answer')->nullable();
            // $table->string('b_fourth_degree_answer')->nullable();
            // $table->string('34_if_yes')->nullable();

            $table->string('question_34a')->nullable();
            $table->string('question_34b')->nullable();
            $table->string('response_34')->nullable();


            // Q35a
            // $table->string('a_found_guilty')->nullable();
            // $table->string('guilty_yes')->nullable();
            // $table->string('b_criminally_charged')->nullable();
            // $table->string('case_date_filed')->nullable();
            // $table->string('case_status')->nullable();

            $table->string('question_35a')->nullable();
            $table->string('response_35a')->nullable();

            $table->string('question_35b')->nullable();
            $table->string('response_35b_date')->nullable();
            $table->string('response_35b_status')->nullable();

            // Q36
            // $table->string('36_convited_answer')->nullable();
            // $table->string('36_if_yes')->nullable();

            $table->string('question_36')->nullable();
            $table->string('response_36')->nullable();

            // Q37
            // $table->string('37_service')->nullable();
            // $table->string('37_if_yes')->nullable();

            $table->string('question_37')->nullable();
            $table->string('response_37')->nullable();

            // Q38
            // $table->string('a_candidate')->nullable();
            // $table->string('candidate_yes')->nullable();

            // $table->string('b_resigned')->nullable();
            // $table->string('resigned_yes')->nullable();

            $table->string('question_38a')->nullable();
            $table->string('response_38a')->nullable();

            $table->string('question_38b')->nullable();
            $table->string('response_38b')->nullable();


            // Q39
            // $table->string('39_status')->nullable();
            // $table->string('39_if_yes')->nullable();

            $table->string('question_39')->nullable();
            $table->string('response_39')->nullable();

            // Q40
            // $table->string('a_indigenous')->nullable();
            // $table->string('indigenous_yes')->nullable();
            // $table->string('b_disability')->nullable();
            // $table->string('disability_yes')->nullable();
            // $table->string('c_solo')->nullable();
            // $table->string('solo_parent_yes')->nullable();


            $table->string('question_40a')->nullable();
            $table->string('response_40a')->nullable();

            $table->string('question_40b')->nullable();
            $table->string('response_40b')->nullable();

            $table->string('question_40c')->nullable();
            $table->string('response_40c')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('personal_declarations');
    }

};
