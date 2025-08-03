<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OnFundedPlantilla extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'on_funded_plantilla';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'job_batches_rsp_id',
        'fileUpload',
        'PositionID',
        'ItemNo'
    ];

    public function jobBatch()
    {
        return $this->belongsTo(JobBatchesRsp::class, 'job_batches_rsp_id');
    }
}
