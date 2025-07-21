<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Personal_declarations extends Model
{

    protected $table = 'personal_declarations';

    protected $fillable = [
        // Q34
        'nPersonalInfo_id',
        'a_third_degree_answer',
        'b_fourth_degree_answer',
        '34_if_yes',

        // Q35
        'a_found_guilty',
        'guilty_yes',
        'b_criminally_charged',
        'case_date_filed',
        'case_status',

        // Q36
        '36_convited_answer',
        '36_if_yes',

        // Q37
        '37_service',
        '37_if_yes',

        // Q38
        'a_candidate',
        'candidate_yes',
        'b_resigned',
        'resigned_yes',

        // Q39
        '39_status',
        '39_if_yes',

        // Q40
        'a_indigenous',
        'indigenous_yes',
        'b_disability',
        'disability_yes',
        'c_solo',
        'solo_parent_yes',
    ];
    // Relationship to nPersonalInfo
    public function personalInfo()
    {
        return $this->belongsTo(nPersonal_info::class, 'nPersonalInfo_id');
    }

}
