<?php

namespace App\Models\excel;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Education_background extends Model
{
  use HasFactory;

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

    protected static function newFactory()
    {
        return \Database\Factories\EducationBackgroundFactory::new();
    }
    public function personalInfo()
    {
        return $this->belongsTo(nPersonal_info::class, 'nPersonalInfo_id');
    }
}
