<?php

namespace App\Services\ShopImportation\Factories;

use App\Services\ShopImportation\Enums\EventsEnum;
use App\Services\ShopImportation\Events\ProductsImportationCsvEvent;

abstract class EventsFactory
{
    static public function getInstance(string $resource, string $type) {
        switch ("$resource.$type") {
            case EventsEnum::PRODUCTS_CSV:
                return new ProductsImportationCsvEvent();
        }
    }
}

