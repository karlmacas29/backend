<?php

namespace App\Models\excel;


use Illuminate\Database\Eloquent\Model;
use Database\Factories\SkillNonAcademicFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class skill_non_academic extends Model
{
    //
    use HasFactory;
    protected $table = 'skill_non_academic';

    protected $fillable =[
        'nPersonalInfo_id',
        'skill',
        'non_academic',
        'organization'
    ];


    // Relationship to nPersonalInfo
    public function personalInfo()
    {
        return $this->belongsTo(nPersonal_info::class, 'nPersonalInfo_id');
    }

    protected static function newFactory()
    {
        return SkillNonAcademicFactory::new();
    }
}
