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

    /**
     * Default allowed filters for models. Individual models may override this.
     *
     * @var array
     */
    protected static array $filters = [];

    /**
     * Return model filters
     *
     * @return array
     */
    public static function filters(): array
    {
        return static::$filters;
    }
}
