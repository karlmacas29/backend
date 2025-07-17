<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Civil_service_eligibity extends Model
{
    //

    protected $table = 'nCivilServiceEligibity';

    protected  $fillable =[
        'nPersonalInfo_id',
        'eligibility',
        'rating',
         'date_of_examination',
         'place_of_examination',
         'license_number',
         'date_of_validity',

    ];


    // Relationship to nPersonalInfo
    public function personalInfo()
    {
        return $this->belongsTo(nPersonal_info::class, 'nPersonalInfo_id');
    }
}
