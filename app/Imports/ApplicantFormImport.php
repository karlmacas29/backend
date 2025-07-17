<?php

namespace App\Imports;


use App\Imports\nFamilySheet;
use App\Imports\PersonalInformationSheet;
use App\Imports\Work_experience_sheet;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;


class ApplicantFormImport implements WithMultipleSheets
{

     protected $nPersonalInfoId;

    public function __construst($nPersonalInfoId= null ){

        $this->nPersonalInfoId = $nPersonalInfoId;
    }

    public function sheets(): array
    {

        return [
            'Personal Information' => new PersonalInformationSheet($this),
            'Family Background' => new nFamilySheet($this),
            'Children' => new Children_sheet($this),
            'Educational Background' => new Education_background_sheet($this),
            'Civil Service Eligibity' => new Civil_service_eligibity_sheet($this),
            'Work Experience' => new  Work_experience_sheet($this),
            'Voluntary Work' => new Voluntary_work_sheet($this),
            'Learning and Development' => new Learning_development_sheet($this),
            'Skill and Non academic' => new skill_non_academic_sheet($this),
        ];
    }

    public function setPersonalInfoId($id){
         $this->nPersonalInfoId = $id;
    }

    public function getPersonalInfoId(){

        return $this->nPersonalInfoId;
    }
}
