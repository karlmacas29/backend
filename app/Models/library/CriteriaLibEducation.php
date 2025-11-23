<?php

namespace App\Models\library;

use Illuminate\Database\Eloquent\Model;

class CriteriaLibEducation extends Model
{
    protected $table =  'criteria_library_education';

    protected $fillable = [
        'criteria_library_id',
        'weight',
        'description',
        'percentage'
    ];


    public function criteriaLibrary()
    {
        return $this->belongsTo(CriteriaLibrary::class, 'criteria_library_id');
    }
}
