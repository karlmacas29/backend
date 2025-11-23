<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class rating_score extends Model
{
    //

    protected $table = 'rating_score';
    protected $fillable = [
        'user_id',
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
        'submitted',
        'ControlNo',
        'rater_name'
    ];

    // protected $casts = [
    //     'behavioral_score' => 'numeric',
    // ];

    public function applicant()
    {
        return $this->belongsTo(\App\Models\excel\nPersonal_info::class, 'nPersonalInfo_id', 'id');
    }
}
