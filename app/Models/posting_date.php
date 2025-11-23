<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class posting_date extends Model
{
    //

     protected $table ='posting_date';


    protected $fillable = [

        'post_date',
        'end_date'
    ];
}
