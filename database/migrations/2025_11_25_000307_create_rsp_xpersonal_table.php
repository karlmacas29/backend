<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    // public function up(): void
    // {
    //     Schema::create('rsp_xpersonal', function (Blueprint $table) {
    //         // $table->id();
    //         $table->string('ControlNo')->nullable();
    //         $table->string('Surname')->nullable();
    //         $table->string('Firstname')->nullable();
    //         $table->string('Middlename')->nullable();
    //         $table->string('Sex')->nullable();
    //         $table->string('CivilStatus')->nullable();
    //         $table->string('MaidenName')->nullable();
    //         $table->string('SpouseName')->nullable();
    //         $table->string('Occupation')->nullable();
    //         $table->string('TINNo')->nullable();
    //         $table->string('GSISNo')->nullable();
    //         $table->string('PAGIBIGNo')->nullable();
    //         $table->string('SSSNo')->nullable();
    //         $table->string('PHEALTHNo')->nullable();
    //         $table->string('Citizenship')->nullable();
    //         $table->string('Religion')->nullable();
    //         $table->date('BirthDate')->nullable();
    //         $table->string('BirthPlace')->nullable();
    //         $table->string('Heights')->nullable();
    //         $table->string('Weights')->nullable();
    //         $table->string('BloodType')->nullable();
    //         $table->string('Address')->nullable();
    //         $table->string('TelNo')->nullable();
    //         $table->string('FatherName')->nullable();
    //         $table->string('FatherBirth')->nullable();
    //         $table->string('MotherName')->nullable();
    //         $table->string('MotherBirth')->nullable();
    //         $table->text('Skills')->nullable();
    //         $table->text('Qualifications')->nullable();
    //         $table->string('Q1')->nullable();
    //         $table->string('R11')->nullable();
    //         $table->string('Q11')->nullable();
    //         $table->string('R1')->nullable();
    //         $table->string('Q2')->nullable();
    //         $table->string('Q22')->nullable();
    //         $table->string('R2')->nullable();
    //         $table->string('Q3')->nullable();
    //         $table->string('R3')->nullable();
    //         $table->string('Q4')->nullable();
    //         $table->string('R4')->nullable();
    //         $table->string('Q5')->nullable();
    //         $table->string('R5')->nullable();
    //         $table->string('Q6')->nullable();
    //         $table->string('R6')->nullable();
    //         $table->string('Q7')->nullable();
    //         $table->string('R7')->nullable();
    //         $table->string('Tax')->nullable();
    //         $table->string('IssuedAt')->nullable();
    //         $table->date('IssuedOn')->nullable();
    //         $table->date('DateAccom')->nullable();
    //         $table->string('Pics')->nullable();
    //         $table->string('PMID')->nullable();
    //         $table->timestamps();
    //     });

    //     // ✅ COPY ALL DATA FROM xpersonal → rsp_xpersonal
    //     DB::statement("
    //         INSERT INTO rsp_xpersonal (
    //             ControlNo, Surname, Firstname, Middlename, Sex, CivilStatus, MaidenName,
    //             SpouseName, Occupation, TINNo, GSISNo, PAGIBIGNo, SSSNo, PHEALTHNo,
    //             Citizenship, Religion, BirthDate, BirthPlace, Heights, Weights, BloodType,
    //             Address, TelNo, FatherName, FatherBirth, MotherName, MotherBirth,
    //             Skills, Qualifications, Q1, R11, Q11, R1, Q2, Q22, R2, Q3, R3, Q4, R4,
    //             Q5, R5, Q6, R6, Q7, R7, Tax, IssuedAt, IssuedOn, DateAccom, Pics, PMID,
    //             created_at, updated_at
    //         )
    //         SELECT
    //             ControlNo, Surname, Firstname, Middlename, Sex, CivilStatus, MaidenName,
    //             SpouseName, Occupation, TINNo, GSISNo, PAGIBIGNo, SSSNo, PHEALTHNo,
    //             Citizenship, Religion, BirthDate, BirthPlace, Heights, Weights, BloodType,
    //             Address, TelNo, FatherName, FatherBirth, MotherName, MotherBirth,
    //             Skills, Qualifications, Q1, R11, Q11, R1, Q2, Q22, R2, Q3, R3, Q4, R4,
    //             Q5, R5, Q6, R6, Q7, R7, Tax, IssuedAt, IssuedOn, DateAccom, Pics, PMID,
    //             GETDATE(), GETDATE()
    //         FROM xPersonal
    //     ");
    // }

    // public function down(): void
    // {
    //     Schema::dropIfExists('rsp_xpersonal');
    // }
};
