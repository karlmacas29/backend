<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TempRegAppointmentReorgExt extends Model
{
    // Explicit table name
    protected $table = 'tempRegAppointmentReorgExt';

    // Explicit primary key
    protected $primaryKey = 'ID';

    // If the table does not use Laravel's created_at/updated_at
    public $timestamps = false;

    // Allow mass assignment for all fields
    protected $fillable = [
        'ControlNo',
        'PresAppro',
        'PrevAppro',
        'SalAuthorized',
        'OtherComp',
        'SupPosition',
        'HSupPosition',
        'Tool',
        'Contact1',
        'Contact2',
        'Contact3',
        'Contact4',
        'Contact5',
        'Contact6',
        'ContactOthers',
        'Working1',
        'Working2',
        'WorkingOthers',
        'DescriptionSection',
        'DescriptionFunction',
        'StandardEduc',
        'StandardExp',
        'StandardTrain',
        'StandardElig',
        'Supervisor',
        'Core1',
        'Core2',
        'Core3',
        'Corelevel1',
        'Corelevel2',
        'Corelevel3',
        'Corelevel4',
        'Leader1',
        'Leader2',
        'Leader3',
        'Leader4',
        'leaderlevel1',
        'leaderlevel2',
        'leaderlevel3',
        'leaderlevel4',
        'structureid',
    ];


    public function xService()
    {
        return $this->hasOne(xService::class, 'ControlNo', 'ControlNo'); // adjust keys
    }
}
