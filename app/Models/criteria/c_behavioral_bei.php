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
        'description'

    ];
    // This will automatically convert JSON <-> array
    protected $casts = [
        'description' => 'array',
    ];

    public function criteriaRating()
    {
        return $this->belongsTo(criteria_rating::class, 'criteria_rating_id');
    }
}
