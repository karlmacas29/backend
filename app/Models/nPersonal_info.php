<?php

namespace App\Models;

use App\Models\nFamily;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class nPersonal_info extends Model
{

    use HasFactory;
    //

    protected  $table = 'nPersonalInfo';

    protected $fillable = [
        'firstname',
        'lastname',
        'image_path'

    ];

    public function family(){

        return $this->hasMany(nFamily::class);

    }

    public function children(){

        return $this->hasMany(Children::class);
    }

    public function education()
    {

        return $this->hasMany(Education_background::class);
    }

    public function eligibity(){

        return $this->hasMany(Civil_service_eligibity::class);
    }

    public function work_experience()
    {

        return $this->hasMany(Work_experience::class);
    }

    public function voluntary_work(){

        return $this->hasMany(Voluntary_work::class);
    }
    public function training(){

        return $this->hasMany(Learning_development::class);
    }

}


