<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Relations\Pivot;

class Job_batches_user extends Pivot
{
    //

     // this table is pivot
    protected $table = 'job_batches_user';

    protected $fillable = [
        'user_id',
        'job_batches_rsp_id',
        'status', // newly added field
        'created_at',
        'updated_at',
    ];
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
        // adjust 'user_id' if your foreign key is different
    }
}
