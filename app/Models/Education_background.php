<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Education_background extends Model
{
    //

    protected $table = 'nEducation';

    protected $fillable = [
        'nPersonalInfo_id',
        'school_name',
        'degree',
        'attendance_from',
        'attendance_to',
        'highest_units',
        'year_graduated',
        'scholarship',
        'level'

    ];
    public function personalInfo()
    {
        return $this->belongsTo(nPersonal_info::class, 'nPersonalInfo_id');
    }
}
