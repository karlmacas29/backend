<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Children extends Model
{
    //

    protected $table ='nChildren';

    protected $fillable =[
     'child_name',
     'birth_date',
    'nPersonalInfo_id',
    ];

    // Relationship to nPersonalInfo
    public function personalInfo()
    {
        return $this->belongsTo(nPersonal_info::class, 'nPersonalInfo_id');
    }
}
