<?php

namespace App\Models\excel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Personal_declarations extends Model
{
    use HasFactory;
    protected $table = 'personal_declarations';

    protected $fillable = [
        // Q34
        'nPersonalInfo_id',

        'question_34a',

        'question_34b',
        'response_34',

        // Q35
        'question_35a',
        'response_35a',

        'question_35b',
        'response_35b_date',
        'response_35b_status',

        // Q36
        'question_36',
        'response_36',

        // Q37
        'question_37',
        'response_37',

        // Q38
        'question_38a',
        'response_38a',

        'question_38b',
        'response_38b',

        // Q39
        'question_39',
        'response_39',

        // Q40
        'question_40a',
        'response_40a',

        'question_40b',
        'response_40b',

        'question_40c',
        'response_40c',
    ];
    // Relationship to nPersonalInfo
    public function personalInfo()
    {
        return $this->belongsTo(nPersonal_info::class, 'nPersonalInfo_id');
    }
    protected static function newFactory()
    {
        return \Database\Factories\PersonalDeclarationsFactory::new();
    }
}
