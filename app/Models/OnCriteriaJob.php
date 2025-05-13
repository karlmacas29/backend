<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OnCriteriaJob extends Model
{
    use HasFactory;

    protected $table = 'on_criteria_job';

    protected $fillable = [
        'PositionID',
        'EduPercent',
        'EliPercent',
        'TrainPercent',
        'ExperiencePercent',
        'Education',
        'Eligibility',
        'Training',
        'Experience',
    ];
}
