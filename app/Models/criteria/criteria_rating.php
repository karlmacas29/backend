<?php

namespace App\Models\criteria;

use App\Models\JobBatchesRsp;
use Illuminate\Database\Eloquent\Model;

class criteria_rating extends Model
{
    //

    protected $table = 'criteria_rating';

    protected $fillable =[
            'job_batches_rsp_id',
            'status',
    ];

    public function jobBatch()
    {
        return $this->belongsTo(JobBatchesRsp::class, 'job_batches_rsp_id','id');
    }

    public function educations(){
        return $this->hasMany(c_education::class);
    }

    public function experiences(){

        return $this->hasMany(c_experience::class);
    }

    public function trainings(){

        return $this->hasMany(c_training::class);
    }


    public function performances(){

        return $this->hasMany(c_performance::class);
    }

    public function behaviorals(){

        return $this->hasMany(c_behavioral_bei::class);

    }
}
