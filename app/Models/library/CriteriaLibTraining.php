<?php

namespace App\Models\library;

use Illuminate\Database\Eloquent\Model;

class CriteriaLibTraining extends Model
{
    //

    protected $table =  'criteria_library_training';

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
