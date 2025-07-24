<?php

namespace App\Models\criteria;

use Illuminate\Database\Eloquent\Model;

class c_behavioral_bei extends Model
{
    //

    protected $table = 'c_behavioral_bei';

    protected $fillable =[
        'criteria_rating_id',
        'Rate',
        'Title',
        'Description',

    ];
}
