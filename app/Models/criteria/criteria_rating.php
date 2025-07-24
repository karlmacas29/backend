<?php

namespace App\Models\criteria;

use Illuminate\Database\Eloquent\Model;

class criteria_rating extends Model
{
    //

    protected $table = 'criteria_rating';

    protected $fillable =[
            'job_batches_rsp_id',
            'status',
    ];
}
