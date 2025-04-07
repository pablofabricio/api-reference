<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class ShopImportationLog extends Model
{
    protected $fillable = [
        'status',
        'errors',
        'request_id',
    ];

    protected $casts = [
        'errors' => 'json',
    ];
}
