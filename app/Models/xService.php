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

    public function active()
    {
        return $this->hasMany(vwActive::class, 'ControlNo', 'ControlNo');
    }
    public function posting_date()
    {
        return $this->hasMany(posting_date::class, 'ControlNo', 'ControlNo');
    }

    public function xPersonal()
    {
        return $this->hasMany(xPersonal::class, 'ControlNo', 'ControlNo');
    }
    public function tempRegAppointments()
    {
        return $this->hasMany(TempRegAppointmentReorg::class, 'ControlNo', 'ControlNo');
    }
    public function tempRegAppointmentReorgExt()
    {
        return $this->hasMany(TempRegAppointmentReorgExt::class, 'ControlNo', 'ControlNo');
    }
}
