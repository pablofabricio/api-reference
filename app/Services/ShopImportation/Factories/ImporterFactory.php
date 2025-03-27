<?php

namespace App\Services\ShopImportation\Factories;

use App\Services\ShopImportation\Enums\ImporterResourcesEnum;
use App\Services\ShopImportation\Importers\ProductImporter;

abstract class ImporterFactory
{
    static public function getInstance(string $type) {
        switch ($type) {
            case ImporterResourcesEnum::PRODUCTS:
                return app(ProductImporter::class);
        }
    }
}
