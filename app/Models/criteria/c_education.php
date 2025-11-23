<?php

namespace App\Models\criteria;

use Illuminate\Database\Eloquent\Model;

class c_education extends Model
{
    //


    protected $table = 'c_education';

    protected $fillable = [
        'criteria_rating_id',
        'weight',
        'description',
        'percentage'


    ];
    // This will automatically convert JSON <-> array
    // protected $casts = [
    //     'description' => 'array',
    // ];

    public function criteriaRating()
    {
        return $this->belongsTo(criteria_rating::class, 'criteria_rating_id');
    }
}
