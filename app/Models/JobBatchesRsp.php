<?php

namespace App\Models;

use App\Models\Submission;
use App\Models\OnFundedPlantilla;
use App\Models\excel\nPersonal_info;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use App\Models\criteria\criteria_rating;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class JobBatchesRsp extends Model
{
    use HasFactory;

    protected $table = 'job_batches_rsp'; // this is the job_post

    protected $fillable = [
        'Office',
        'Office2',
        'Group',
        'Division',
        'Section',
        'Unit',
        'Position',
        'post_date',
        'PositionID',
        'isOpen',
        'end_date',
        'PageNo',
        'ItemNo',
        'SalaryGrade',
        'salaryMin',
        'salaryMax',
        'level',
        'Education',
        'Eligibility',
        'Training',
        'Experience',
        'fileUpload',
        'status',
        'tblStructureDetails_ID',


    ];


    protected static function booted()
    {
        static::deleting(function ($jobPost) {
            // Delete related criteria jobs
            OnCriteriaJob::where('PositionID', $jobPost->PositionID)
                ->where('ItemNo', $jobPost->ItemNo)
                ->delete();

            // Delete related funded plantilla and its file
            $plantillas = OnFundedPlantilla::where('PositionID', $jobPost->PositionID)
                ->where('ItemNo', $jobPost->ItemNo)
                ->get();

            foreach ($plantillas as $plantilla) {
                if ($plantilla->fileUpload && Storage::disk('public')->exists($plantilla->fileUpload)) {
                    Storage::disk('public')->delete($plantilla->fileUpload);
                }
                $plantilla->delete();
            }
        });
        // static::creating(function ($submission) {
        //     if (is_null($submission->status)) {
        //         $submission->status = 'pending';
        //     }
        // });
    }


    public function users()
    {
        return $this->belongsToMany(User::class, 'job_batches_user', 'job_batches_rsp_id', 'user_id')->withTimestamps();
    }

    public function personal_info()
    {
        return $this->hasMany(nPersonal_info::class,);
    }

    public function criteriaRatings()
    {
        return $this->hasMany(criteria_rating::class, 'job_batches_rsp_id');
    }

    // public function applicants()
    // {
    //     return $this->belongsToMany(nPersonal_info::class, 'submission', 'job_batches_rsp_id', 'nPersonalInfo_id',)
    //         ->withTimestamps();
    // }

    public function applicants()
    {
        return $this->belongsToMany(nPersonal_info::class, 'submission', 'job_batches_rsp_id', 'nPersonalInfo_id')
            ->withTimestamps();
    }

    // public function onCriteriaJobs(){

    //     return $this->hasMany(OnCriteriaJob::class, 'job_batches_rsp_id');
    // }


    public function criteria()
    {
        return $this->hasOne(OnCriteriaJob::class, 'job_batches_rsp_id');
    }

    public function plantilla()
    {
        return $this->hasOne(OnFundedPlantilla::class, 'job_batches_rsp_id');
    }


    // public function  funded_plantilla()
    // {

    //     return $this->hasMany(OnFundedPlantilla::class, 'job_batches_rsp_id');
    // }
    // public function funded()
    // {
    //     return $this->hasMany(OnFundedPlantilla::class, 'PositionID', 'PositionID');
    // }

    public function submissions()
    {
        return $this->hasMany(Submission::class, 'job_batches_rsp_id', 'id');
    }
}
