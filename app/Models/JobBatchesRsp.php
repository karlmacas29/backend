<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobBatchesRsp extends Model
{
    use HasFactory;

    protected $table = 'job_batches_rsp';

    protected $fillable = [
        'Office',
        'Office2',
        'Group',
        'Division',
        'Section',
        'Unit',
        'Position',
        'post_date',
        'PositionID',
        'isOpen',
        'end_date',
        'PageNo',
        'ItemNo',
        'SalaryGrade',
        'salaryMin',
        'salaryMax',
        'level',
    ];

    public function users()
    {
        return $this->hasMany(User::class,);
    }
}
