<?php

namespace App\Models\criteria;

use Illuminate\Database\Eloquent\Model;

class c_experience extends Model
{
    //
    protected $table = 'c_experience';

    protected $fillable = [
        'criteria_rating_id',
        'Rate',
        'Min_qualification',
        'Title',
        'With_experience',
        'Without_experience',

    ];
}
