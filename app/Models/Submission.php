<?php

namespace App\Models;

use App\Models\excel\nFamily;
use App\Models\excel\Children;
use App\Models\excel\nPersonal_info;
use Illuminate\Database\Eloquent\Model;
use App\Models\excel\Education_background;
use App\Models\excel\Civil_service_eligibity;
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
    // public function children()
    // {
    //     return $this->belongsTo(Children::class, 'nPersonalInfo_id');
    // }

    // public function education()
    // {
    //     return $this->belongsTo(Education_background::class, 'nPersonalInfo_id');
    // }

    // public function family()
    // {
    //     return $this->belongsTo(nFamily::class, 'nPersonalInfo_id');
    // }
    // protected static function booted()
    // {
    //     static::creating(function ($submission) {
    //         if (is_null($submission->status)) {
    //             $submission->status = 'pending';
    //         }
    //     });
    // }
    // public function eligibity()
    // {
    //     return $this->belongsTo(Civil_service_eligibity::class, 'nPersonalInfo_id');
    // }

    public function jobPost()
    {
        return $this->belongsTo(JobBatchesRsp::class, 'job_batches_rsp_id');
    }

    public function job_batch_rsp()
    {
        return $this->belongsTo(JobBatchesRsp::class, 'job_batches_rsp_id', 'id');
    }

}

