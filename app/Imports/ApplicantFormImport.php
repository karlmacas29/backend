<?php

namespace App\Imports;


use file;
use App\Imports\nFamilySheet;
use App\Imports\Work_experience_sheet;
use App\Imports\PersonalInformationSheet;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;



class ApplicantFormImport implements WithMultipleSheets
{
    protected $jobBatchId;
    protected $nPersonalInfoId;
    protected $fileName;
    protected $ImageValue;

    // public function __construct($jobBatchId = null)
    // {
    //     $this->jobBatchId = $jobBatchId;
    // }
    // public function __construst($nPersonalInfoId= null ){

    //     $this->nPersonalInfoId = $nPersonalInfoId;
    // }

    public function __construct($jobBatchId = null, $nPersonalInfoId = null, $fileName = null, $ImageValue = null)
    {
        $this->jobBatchId = $jobBatchId;
        $this->nPersonalInfoId = $nPersonalInfoId;
        $this->fileName = $fileName;
        $this->ImageValue = $ImageValue;

    }


    public function sheets(): array
    {

        return [
            'Personal Information' => new PersonalInformationSheet($this, $this->jobBatchId, $this->fileName, $this->ImageValue),
            'Family Background' => new nFamilySheet($this),
            'Children' => new Children_sheet($this),
            'Educational Background' => new Education_background_sheet($this),
            'Civil Service Eligibity' => new Civil_service_eligibity_sheet($this),
            'Work Experience' => new  Work_experience_sheet($this),
            'Voluntary Work' => new Voluntary_work_sheet($this),
            'Learning and Development' => new Learning_development_sheet($this),
            'Skill and Non academic' => new skill_non_academic_sheet($this),
            'Personal Declarations' => new Personal_declarations_sheet($this),

        ];
    }

    public function setPersonalInfoId($id){
         $this->nPersonalInfoId = $id;
    }

    public function getPersonalInfoId(){

        return $this->nPersonalInfoId;
    }
    public function getFileName()
    {
        return $this->fileName;
    }

    public function getImageValue()
    {
        return $this->ImageValue;
    }
}

