<?php

namespace App\Models;

use App\Models\excel\nFamily;
use App\Models\excel\Children;
use App\Models\excel\nPersonal_info;
use Illuminate\Database\Eloquent\Model;
use App\Models\excel\Education_background;
use App\Models\excel\Civil_service_eligibity;
use App\Models\excel\Learning_development;
use App\Models\excel\Work_experience;
use Illuminate\Database\Eloquent\Relations\Pivot;

// class Submission extends Pivot
class Submission extends Model
{
    //

    protected $table ='submission'; // applicant apply on the job post
    protected $fillable =[
        'nPersonalInfo_id', // applicant
        'job_batches_rsp_id',

        'education_remark',
        'experience_remark',
        'training_remark',
        'eligibility_remark',
         'status',
        'ControlNo',

        'education_qualification',
        'experience_qualification',
        'training_qualification',
        'eligibility_qualification',

    ];

    protected $casts = [
        'education_qualification' => 'array',
        'experience_qualification' => 'array',
        'training_qualification' => 'array',
        'eligibility_qualification' => 'array',
    ];

    public $timestamps = true; // or just remove if not set

    public function nPersonalInfo()
    {
        return $this->belongsTo(nPersonal_info::class, 'nPersonalInfo_id');
    }
    public function ControlNo() // external applicants
    {
        return $this->belongsTo(xPersonal::class, 'ControlNo', 'ControlNo');
    }

    public function xPersonal() // external applicants
    {
        return $this->belongsTo(xPersonal::class, 'ControlNo', 'ControlNo');
    }


    public function jobPost()
    {
        return $this->belongsTo(JobBatchesRsp::class, 'job_batches_rsp_id');
    }

    public function job_batch_rsp()
    {
        return $this->belongsTo(JobBatchesRsp::class, 'job_batches_rsp_id', 'id');
    }


    public function schedules()
    {
        return $this->hasMany(Schedule::class, 'submission_id', 'id');
    }



    // ✅ NEW: Get education records by IDs
    public function getEducationRecords()
    {
        if (empty($this->education_qualification)) {
            return collect();
        }

        return Education_background::whereIn('id', $this->education_qualification)->get();
    }

    // ✅ NEW: Get experience records by IDs
    public function getExperienceRecords()
    {
        if (empty($this->experience_qualification)) {
            return collect();
        }

        return Work_experience::whereIn('id', $this->experience_qualification)->get();
    }

    // ✅ NEW: Get training records by IDs
    public function getTrainingRecords()
    {
        if (empty($this->training_qualification)) {
            return collect();
        }

        return Learning_development::whereIn('id', $this->training_qualification)->get();
    }

    // ✅ NEW: Get eligibility records by IDs
    public function getEligibilityRecords()
    {
        if (empty($this->eligibility_qualification)) {
            return collect();
        }

        return Civil_service_eligibity::whereIn('id', $this->eligibility_qualification)->get();
    }
}

