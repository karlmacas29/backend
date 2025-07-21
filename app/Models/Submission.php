<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Submission extends Model
{
    //

    protected $table ='submission';

    protected $fillable =[
            'nPersonalInfo_id',
            'job_batches_rsp_id'
    ];
}
