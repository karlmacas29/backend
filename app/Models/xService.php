<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class xService extends Model
{
    //
    protected $table = 'xService';
    public $timestamps = false;
    protected $casts = [
        'ControlNo' => 'string',
        'FromDate' => 'string',
        'ToDate' => 'string',
    ];

    public function plantilla()
    {
        return $this->hasMany(vwplantillastructure::class, 'ControlNo', 'ControlNo');
    }

    public function tempRegAppointments()
    {
        return $this->hasMany(TempRegAppointmentReorg::class, 'ControlNo', 'ControlNo');
    }
}
