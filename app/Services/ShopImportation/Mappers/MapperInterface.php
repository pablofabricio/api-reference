<?php

namespace App\Services\ShopImportation\Mappers;

interface MapperInterface
{
    /**
     * @return array
     */
    public static function map(array $data): array;
}
