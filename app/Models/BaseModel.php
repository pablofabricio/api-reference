<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

abstract class BaseModel extends Model
{
    /**
     * Default pagination size.
     *
     * @var int
     */
    protected $perPage = 25;
}
