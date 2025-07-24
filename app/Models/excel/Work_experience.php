<?php

namespace App\Models\excel;


use Illuminate\Database\Eloquent\Model;

class Work_experience extends Model
{
    //

    protected $table = 'nWorkExperience';

    protected $fillable =[
        'nPersonalInfo_id',
        'work_date_from',
        'work_date_to',
        'position_title',
        'department',
        'monthly_salary',
        'salary_grade',
        'status_of_appointment',
        'government_service',


    ];

    // Relationship to nPersonalInfo
    public function personalInfo()
    {
        return $this->belongsTo(nPersonal_info::class, 'nPersonalInfo_id');
    }

}
