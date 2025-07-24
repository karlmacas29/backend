<?php

namespace App\Models\criteria;

use Illuminate\Database\Eloquent\Model;

class c_performance extends Model
{
    //

    protected $table = 'c_performance';

    protected $fillable = [
        'criteria_rating_id',
        'Rate',
        'Title',
        'Outstanding_rating',
        'Very_Satisfactory',
        'Below_rating',

    ];
}
