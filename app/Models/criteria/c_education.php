<?php

namespace App\Models\criteria;

use Illuminate\Database\Eloquent\Model;

class c_education extends Model
{
    //


    protected $table = 'c_education';

    protected $fillable = [
        'criteria_rating_id',
        'Rate',
        'Min_qualification',
        'Title',
        'Description',

    ];
}
