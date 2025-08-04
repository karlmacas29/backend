<?php

namespace App\Models;

use App\Models\excel\nPersonal_info;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;

// class Submission extends Pivot
class Submission extends Model
{
    //

    protected $table ='submission';
    protected $fillable =[
        'nPersonalInfo_id',
        'job_batches_rsp_id',
        'education_remark',
        'experience_remark',
        'training_remark',
        'eligibility_remark',
         'status'
    ];

    public function nPersonalInfo()
    {
        return $this->belongsTo(nPersonal_info::class, 'nPersonalInfo_id');
    }
    // protected static function booted()
    // {
    //     static::creating(function ($submission) {
    //         if (is_null($submission->status)) {
    //             $submission->status = 'pending';
    //         }
    //     });
    // }
}

