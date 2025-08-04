<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class rating_score extends Model
{
    //

    protected $table = 'rating_score';
    protected $fillable = [
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
    ];
}
