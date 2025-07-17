<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class vwplantillastructure extends Model
{
    //
    protected $table = 'vwplantillaStructure'; // Assuming this is the table name

    protected $fillable = [
        'ControlNo',
        'office',
        'office2',
        'Groups',
        'Division',
        'Section',
        'Unit',
        'ItemNo',
        'Position',
        'office_sort',
        'Ordr',
        'groupordr',
        'divordr',
        'secordr',
        'unitordr',
        'Status',
    ];
    // relationship  one is to one oh vwActive
    public function vwActive()
    {
        return $this->hasOne(vwActive::class, 'ControlNo', 'ControlNo');
        // added explicit keys for clarity:
        // foreign_key on vwActive, local_key on vwplantillastructure
    }
}
