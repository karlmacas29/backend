<?php

namespace App\Models\excel;


use App\Models\JobBatchesRsp;
use App\Models\rating_score;
use App\Models\Submission;
use App\Models\excel\references;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class nPersonal_info extends Model
{

    use HasFactory;
    //

    protected  $table = 'nPersonalInfo';

    protected $fillable = [
        'lastname',
        'firstname',
        'middlename',
        'name_extension',
        'date_of_birth',
        'sex',
        'place_of_birth',
        'height',
        'weight',
        'blood_type',
        'gsis_no',
        'pagibig_no',
        'philhealth_no',
        'sss_no',
        'tin_no',
        'image_path',
        'civil_status',
        'citizenship',
        'citizenship_status',

        'residential_house',
        'residential_street',
        'residential_subdivision',
        'residential_barangay',
        'residential_city',
        'residential_province',
        'residential_zip',

        'permanent_house',
        'permanent_street',
        'permanent_subdivision',
        'permanent_barangay',
        'permanent_city',
        'permanent_province',
        'permanent_zip',
        'excel_file',

        'telephone_number',
        'cellphone_number',
        'email_address',
    ];

    public function family()
    {
        return $this->hasMany(nFamily::class, 'nPersonalInfo_id');
    }

    public function children()
    {
        return $this->hasMany(Children::class, 'nPersonalInfo_id');
    }

    public function education()
    {
        return $this->hasMany(Education_background::class, 'nPersonalInfo_id');
    }

    public function eligibity()
    {
        return $this->hasMany(Civil_service_eligibity::class, 'nPersonalInfo_id');
    }

    public function work_experience()
    {
        return $this->hasMany(Work_experience::class, 'nPersonalInfo_id');
    }

    public function voluntary_work()
    {
        return $this->hasMany(Voluntary_work::class, 'nPersonalInfo_id');
    }

    public function training()
    {
        return $this->hasMany(Learning_development::class, 'nPersonalInfo_id');
    }

    public function personal_declarations()
    {
        return $this->hasMany(Personal_declarations::class, 'nPersonalInfo_id');
    }

    public function skills()
    {
        return $this->hasMany(skill_non_academic::class, 'nPersonalInfo_id');
    }
    public function job_batches_rsp()
    {
        return $this->belongsToMany(
            JobBatchesRsp::class,
            'submission',
            'nPersonalInfo_id',
            'job_batches_rsp_id'
        )->withTimestamps();
    }

    public function submission()
    {
        return $this->belongsTo(Submission::class, 'submission_id');
    }
    public function rating_score()
    {
        return $this->hasOne(rating_score::class, 'nPersonalInfo_id', 'id');
    }
    public function reference()
    {
        return $this->hasMany(references::class, 'nPersonalInfo_id');
    }
}
    // public function upload_file_image()
    // {
    //     return $this->hasMany(Upload_file_image::class);
    // }



