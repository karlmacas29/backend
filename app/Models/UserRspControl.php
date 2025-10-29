<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserRspControl extends Model
{
    use HasFactory;

    protected $table = 'user_rsp_control';

    protected $fillable = [
        'user_id',
        'viewDashboardstat',
        'viewPlantillaAccess',
        'modifyPlantillaAccess',
        'viewJobpostAccess',
        'modifyJobpostAccess',

        'viewActivityLogs',
        'userManagement',
        'viewRater',
        'modifyRater',

        'viewCriteria',
        'modifyCriteria',
        'viewReport'
    ];

    // In UserRspControl.php
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
