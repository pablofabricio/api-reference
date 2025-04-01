<?php

namespace App\Services\ShopImportation\Events;

use App\Services\ShopImportation\Adapters\CsvFileAdapter;
use App\Services\ShopImportation\Enums\ImporterResourcesEnum;
use App\Services\ShopImportation\Mappers\ProductMapper;
use App\Services\ShopImportation\Validators\ProductsCsvValidator;
use Generator;

class ProductsImportationCsvEvent extends ShopImportationEvent
{
    function __construct($item = [])
    {
        $this->resource = ImporterResourcesEnum::PRODUCTS;
        $this->setItem($item);
    }

    public function generateRecords(int $startIndex = 0): \Generator
    {
        $validator = app(ProductsCsvValidator::class);
        $this->fileAdapter = new CsvFileAdapter($this->request['filePath'], $validator);
        
        $index = 0;

        foreach ($this->fileAdapter->read() as $record) {
            if ($index++ < $startIndex) {
                continue; 
            }
            yield ProductMapper::map($record);
        }
    }
}