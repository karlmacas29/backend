<?php

namespace App\Models\excel;


use Illuminate\Database\Eloquent\Model;

class skill_non_academic extends Model
{
    //

    protected $table = 'skill_non_academic';

    protected $fillable =[
        'nPersonalInfo_id',
        'skill',
        'non_academic',
        'organization'
    ];


    // Relationship to nPersonalInfo
    public function personalInfo()
    {
        return $this->belongsTo(nPersonal_info::class, 'nPersonalInfo_id');
    }
}
