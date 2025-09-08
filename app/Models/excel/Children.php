<?php

namespace App\Models\excel;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Children extends Model
{
    //

        use HasFactory;
    protected $table ='nChildren';

    protected $fillable =[
     'child_name',
     'birth_date',
    'nPersonalInfo_id',
    ];

    // Relationship to nPersonalInfo
    public function personalInfo()
    {
        return $this->belongsTo(nPersonal_info::class, 'nPersonalInfo_id');
    }
    // in App\Models\excel\Children.php
    protected static function newFactory()
    {
        return \Database\Factories\ChildrenFactory::new();
    }
}
