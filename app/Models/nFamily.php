<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class nFamily extends Model
{
    //

    protected $table = 'nFamily';

    protected  $fillable = [
        'nPersonalInfo_id',
        'spouse_name',
        'spouse_firstname',
        'spouse_middlename',
        'spouse_extension',
        'spouse_occupation',
        'spouse_employer',
        'spouse_employer_address',
        'spouse_employer_telephone',
        'father_name',
        'father_firstname',
        'father_middlename',
        'father_extension',
        'mother_name',
        'mother_firstname',
        'mother_middlename',
        'mother_maidenname'

    ];

    // Relationship to nPersonalInfo
    public function personalInfo()
    {
        return $this->belongsTo(nPersonal_info::class, 'nPersonalInfo_id');
    }
}
