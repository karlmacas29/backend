<?php

namespace App\Models;

use App\Models\criteria\criteria_rating;
use App\Models\excel\nPersonal_info;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobBatchesRsp extends Model
{
    use HasFactory;

    protected $table = 'job_batches_rsp';

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
    ];

    protected static function booted()
    {
        static::deleting(function ($jobPost) {
            $jobPost->criteriaJobs()->delete();
        });
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

    public function applicants()
    {
        return $this->belongsToMany(nPersonal_info::class, 'submission', 'job_batches_rsp_id', 'nPersonalInfo_id')
            ->withTimestamps();
    }
    public function criteriaJobs()
    {
        return $this->hasMany(OnCriteriaJob::class, 'PositionID', 'PositionID');
    }
}
