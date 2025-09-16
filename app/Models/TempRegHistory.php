<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TempRegHistory extends Model
{
    protected $table = 'temp_reg_appointment_reorg_history'; // actual table name

    protected $primaryKey = 'ID'; // primary key column
    public $incrementing = false; // since it's decimal, not auto-increment
    protected $keyType = 'string'; // or 'int' if you’ll use numeric values

    protected $fillable = [
        'ID',
        'ControlNo',
        'DesigCode',
        'NewDesignation',
        'Designation',
        'SG',
        'Step',
        'Status',
        'OffCode',
        'NewOffice',
        'Office',
        'MRate',
        'ItemNo',
        'Pages',
        'DivCode',
        'SecCode',
        'Official',
        'Renew',
        'StructureID',
        'groupcode',
        'group',
        'unitcode',
        'unit',
        'sepdate',
        'sepcause',
        'vicename',
        'vicecause',
    ];
}
