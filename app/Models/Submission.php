<?php

namespace App\Models;

use App\Models\excel\nPersonal_info;
use Illuminate\Database\Eloquent\Model;

class Submission extends Model
{
    //

    protected $table ='submission';
    protected $fillable =[
        'nPersonalInfo_id',
        'job_batches_rsp_id',
        'education_score',
        'experience_score',
        'training_score',
        'performance_score',
        'behavioral_score',
        'total_qs',
        'grand_total',
         'ranking',
         'status'


    ];

    public function nPersonalInfo()
    {
        return $this->belongsTo(nPersonal_info::class, 'nPersonalInfo_id');
    }
}

