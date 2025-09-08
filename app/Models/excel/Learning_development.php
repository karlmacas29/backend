<?php

namespace App\Models\excel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Learning_development extends Model
{
    //
    use HasFactory;
    protected $table = 'nTrainings';

    protected $fillable =[
        'nPersonalInfo_id',
        'training_title',
        'inclusive_date_from',
        'inclusive_date_to',
        'number_of_hours',
        'type',
        'conducted_by'
    ];

    protected static function newFactory()
    {
        return \Database\Factories\LearningDevelopmentFactory::new();
    }
    // Relationship to nPersonalInfo
    public function personalInfo()
    {
        return $this->belongsTo(nPersonal_info::class, 'nPersonalInfo_id');
    }
}
