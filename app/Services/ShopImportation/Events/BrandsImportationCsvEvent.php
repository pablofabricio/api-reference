<?php

namespace App\Services\ShopImportation\Events;

use App\Services\ShopImportation\Enums\ImporterResourcesEnum;

class BrandsImportationCsvEvent extends ShopImportationEvent
{
    function __construct()
    {
        $this->resource = ImporterResourcesEnum::BRANDS;
    }
}