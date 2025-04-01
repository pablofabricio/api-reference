<?php

namespace App\Services\ShopImportation\Mappers;

use Generator;

interface MapperInterface
{
    public static function map(array $data): array;
}
