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
        'isFunded',
        'isUserM',
        'isRaterM',
        'isCriteria',
        'isDashboardStat',
    ];

    // In UserRspControl.php
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
