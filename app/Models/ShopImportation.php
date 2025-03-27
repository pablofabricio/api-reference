<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class ShopImportation extends Model
{
    protected $fillable = [
        'token',
        'resource',
        'from_id',
        'to_id', 
        'request_id'
    ];
}
