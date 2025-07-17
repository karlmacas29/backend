<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Learning_development extends Model
{
    //

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

    // Relationship to nPersonalInfo
    public function personalInfo()
    {
        return $this->belongsTo(nPersonal_info::class, 'nPersonalInfo_id');
    }
}
