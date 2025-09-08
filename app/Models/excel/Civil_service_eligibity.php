<?php

namespace App\Models\excel;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Civil_service_eligibity extends Model
{
    //
    use HasFactory;

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

    protected static function newFactory()
    {
        return \Database\Factories\CivilServiceEligibityFactory::new();
    }
    // Relationship to nPersonalInfo
    public function personalInfo()
    {
        return $this->belongsTo(nPersonal_info::class, 'nPersonalInfo_id');
    }
}
