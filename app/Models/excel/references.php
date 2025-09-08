<?php

namespace App\Models\excel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class references extends Model
{
    //
    use HasFactory;
    protected $table = 'references';

    protected $fillable = [
        'nPersonalInfo_id',
        'full_name',
        'address',
        'contact_number',
    ];
     // Relationship to nPersonalInfo
     public function personalInfo()
     {
         return $this->belongsTo(nPersonal_info::class, 'nPersonalInfo_id');
     }

    protected static function newFactory()
    {
        return \Database\Factories\referencesFactory::new();
    }
}
