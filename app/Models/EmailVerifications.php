<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailVerifications extends Model
{
    //

    protected $table = 'email_verifications';

    protected $fillable = [
        'email',
        'code',
        'expires_at',
    ];
    protected $casts = [
        'expires_at' => 'datetime',
    ];
}
