<?php

namespace App\Models;

use App\Models\Role;
use App\Models\JobBatchesRsp;
use App\Models\UserRspControl;

use Laravel\Sanctum\HasApiTokens;
use Spatie\Activitylog\LogOptions;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens,LogsActivity;

    // the rater and admin
    // Specify the fillable fields for mass assignment
    protected $fillable = [
        'name',
        'username', // Replace email with username
        'password',
        'position', // Add position to fillable
        'active',   // Add active to fillable
        'role_id',  // Add role_id to fillable
        'remember_token', // Add remember_token to fillable
        'office', // Add office_id to fillable

    ];

    // Specify the hidden fields for serialization
    protected $hidden = [
        'password',
        'remember_token',
    ];

    // Specify the casts for attributes
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'active' => 'boolean', // Cast active as a boolean
    ];

    public function rspControl()
    {
        return $this->hasOne(UserRspControl::class);
    }
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function job_batches_rsp()
    {
        return $this->belongsToMany(
            JobBatchesRsp::class,
            'job_batches_user',
            // 'job_batches_rsp_user',   // correct pivot table name
            'user_id',                // foreign key sa User
            'job_batches_rsp_id'      // foreign key sa JobBatchesRsp
        )->withTimestamps();
    }


    public function office()
    {
        return $this->hasOne(vwplantillaStructure::class, 'office_id', 'office_id');
    }


    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'username', 'position', 'role_id', 'office'])
            ->logOnlyDirty() // logs only changed attributes
            ->useLogName('user')
            ->setDescriptionForEvent(fn(string $eventName) => "User has been {$eventName}");
    }
}
