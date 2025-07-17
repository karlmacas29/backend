<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Relations\Pivot;

class Job_batches_rsp_user extends Pivot
{
    //

    protected $table = 'job_batches_rsp_user';

    protected $fillable = [
        'user_id',
        'job_batches_rsp_id',
        'created_at',
        'updated_at',
    ];
}
