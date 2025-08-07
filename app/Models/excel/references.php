<?php

namespace App\Models\excel;

use Illuminate\Database\Eloquent\Model;

class references extends Model
{
    //

    protected $table = 'references';

    protected $fillable = [
        'nPersonalInfo_id',
        'full_name',
        'address',
        'contact_number',
    ];
     // Relationship to nPersonalInfo
     public function personalInfo()
     {
         return $this->belongsTo(nPersonal_info::class, 'nPersonalInfo_id');
     }
}
