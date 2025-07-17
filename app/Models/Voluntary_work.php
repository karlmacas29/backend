<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Voluntary_work extends Model
{
    //

    protected $table = 'nVoluntaryWork';

    protected $fillable =[
        'nPersonalInfo_id',
        'organization_name',
        'inclusive_date_from',
        'inclusive_date_to',
        'number_of_hours',
        'position'

    ];

    // Relationship to nPersonalInfo
    public function personalInfo()
    {
        return $this->belongsTo(nPersonal_info::class, 'nPersonalInfo_id');
    }
}
