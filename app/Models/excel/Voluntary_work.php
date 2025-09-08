<?php

namespace App\Models\excel;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Voluntary_work extends Model
{
    //
    use HasFactory;
    protected $table = 'nVoluntaryWork';

    protected $fillable =[
        'nPersonalInfo_id',
        'organization_name',
        'inclusive_date_from',
        'inclusive_date_to',
        'number_of_hours',
        'position'

    ];

    // Relationship to nPersonalInfo
    public function personalInfo()
    {
        return $this->belongsTo(nPersonal_info::class, 'nPersonalInfo_id');
    }

    protected static function newFactory()
    {
        return \Database\Factories\VoluntaryWorkFactory::new();
    }
}
