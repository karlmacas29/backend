<?php

namespace App\Models\library;

use Illuminate\Database\Eloquent\Model;

class CriteriaLibrary extends Model
{
    //

    protected $table = 'criteria_library';

    protected $fillable = [

        'sg_max',
        'sg_min'
    ];



    public function criteriaLibEducation()
    {
        return $this->hasMany(CriteriaLibEducation::class, 'criteria_library_id');
    }

    public function criteriaLibExperience()
    {
        return $this->hasMany(CriteriaLibExperience::class, 'criteria_library_id');
    }

    public function criteriaLibTraining()
    {
        return $this->hasMany(CriteriaLibTraining::class, 'criteria_library_id');
    }

    public function criteriaLibPerformance()
    {
        return $this->hasMany(CriteriaLibPerformance::class, 'criteria_library_id');
    }

    public function criteriaLibBehavioral()
    {
        return $this->hasMany(CriteriaLibBehavioral::class, 'criteria_library_id');
}


}
