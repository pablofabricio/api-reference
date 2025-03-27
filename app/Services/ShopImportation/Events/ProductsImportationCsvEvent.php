<?php

namespace App\Services\ShopImportation\Events;

use App\Services\ShopImportation\Adapters\CsvFileAdapter;
use App\Services\ShopImportation\Enums\ImporterResourcesEnum;
use App\Services\ShopImportation\Mappers\ProductMapper;
use App\Services\ShopImportation\Validators\ProductsCsvValidator;

class ProductsImportationCsvEvent extends ShopImportationEvent
{
    function __construct($item = [])
    {
        $this->resource = ImporterResourcesEnum::PRODUCTS;
        $this->setItem($item);
    }

    public function recordsToArray(): array
    {
        $validator = app(ProductsCsvValidator::class);
        $this->fileAdapter = new CsvFileAdapter($this->request['filePath'], $validator);
        
        $fileData = $this->fileAdapter->read();

        return ProductMapper::map($fileData);
    }
}