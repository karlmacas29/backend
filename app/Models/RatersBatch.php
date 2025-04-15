<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class RatersBatch extends Model
{
    use HasFactory;

    protected $table = 'raters_batch'; // Explicitly set the table name

    protected $fillable = [
        'raters',
        'assign_batch',
        'position',
        'office',
        'pending',
        'completed',
    ];
}
