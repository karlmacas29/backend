<?php

namespace App\Models;

use App\Models\excel\nPersonal_info;
use Illuminate\Database\Eloquent\Model;

class ApplicantZip extends Model
{
    //

    protected $table = 'applicant_zips';
    protected $fillable = [
        'nPersonalInfo_id',
        'zip_path',
    ];

    public function nPersonalInfo()
    {
        return $this->belongsTo(nPersonal_info::class, 'nPersonalInfo_id');
    }
}
