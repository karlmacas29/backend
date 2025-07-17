<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class vwActive extends Model
{
    //
     protected $table = 'vwActive';
    public $timestamps = false; // Disable timestamps
    protected $primaryKey = 'ControlNo'; // Set ControlNo as the primary key
    public $incrementing = false; // ControlNo is not auto-incrementing
    protected $keyType = 'string'; // Because ControlNo is a string
    protected $fillable = [
        'ControlNo',
        'PMISNO',
        'Surname',
        'Firstname',
        'Sex',
        'Office',
        'Status',
        'ToDate',
        'MIddlename',
        'BirthDate',
        'Pics',
        'Grades',
        'Steps',
        'Designation',
        'Name1',
        'Name2',
        'Name3',
        'Name4',
        'DesigCode',
        'Charges',
        'RateDay',
        'TelNo',
        'RateMon',
        'Divisions',
        'Sections',
        'FromDate',
        'Address',
        'Renew'
    ];

    public function vwPlantillaStructure()
    {
        return $this->belongsTo(vwplantillastructure::class, 'ControlNo', 'ControlNo');
    }

}
