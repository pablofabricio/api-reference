<?php

namespace App\Services\ShopImportation\Events;

use App\Services\ShopImportation\Enums\ImporterResourcesEnum;

class RedirectsImportationCsvEvent extends ShopImportationEvent
{
    function __construct($item = [])
    {
        $this->resource = ImporterResourcesEnum::REDIRECTS;
        $this->setItem($item);
    }
}