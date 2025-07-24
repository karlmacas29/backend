<?php

namespace App\Models\criteria;

use Illuminate\Database\Eloquent\Model;

class c_training extends Model
{
    //

    protected $table = 'c_training';

    protected $fillable = [
        'criteria_rating_id',
        'Rate',
        'Title',
        'Description',

    ];
}
