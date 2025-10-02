<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OnCriteriaJob extends Model
{
    use HasFactory;

    protected $table = 'on_criteria_job';

    protected $fillable = [
        'job_batches_rsp_id',
        'PositionID',
        'Education',
        'Eligibility',
        'Training',
        'Experience',
        'ItemNo'
    ];
    public function jobBatch()
    {
        return $this->belongsTo(JobBatchesRsp::class, 'job_batches_rsp_id');
    }
}
