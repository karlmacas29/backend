<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    // Specify the fillable fields for mass assignment
    protected $fillable = [
        'name',
        'username', // Replace email with username
        'password',
        'position', // Add position to fillable
        'active',   // Add active to fillable
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
}
