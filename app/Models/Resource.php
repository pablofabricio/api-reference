<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Resource extends Model
{
    protected $fillable = [
        'resource',
        'from_id',
        'to_id',
        'migrations_id',
        'from_token',
        'to_token'
    ];
}
