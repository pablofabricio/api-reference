<?php

namespace App\Services\ShopImportation\Importers;

use App\Services\ShopImportation\DTO\BrandDto;
use App\Services\ShopImportation\Events\BrandsImportationCsvEvent;
use App\Services\ShopImportation\Events\ShopImportationEvent;

class ProductImporter extends ShopImporterService
{
    public function import(ShopImportationEvent $event): void
    {
        $item = $event->getItem();
        
        $brandEvent = new BrandsImportationCsvEvent();
        $brandEvent->setItem(BrandDto::fromArray($item)->toArray());
        $brandEvent->setRequest($event->getRequest());
        parent::import($brandEvent); 
        
        $this->importCategory($item);
        // attribute
        // redirect
        // product 
    }

    private function importCategory(array $item): void
    {
        if ($item['category_1'] ?? false) {
            $category1 = [
                'name' => $item['category_1'],
                'external_id' => $item['category_1'],
            ];
            //$categoryEvent = new BrandsImportationCsvEvent(CategoryDto::fromArray($category1)->toArray());
            //parent::import($categoryEvent);
        }
        
        if ($item['category_2'] ?? false) {
            $category2 = [
                'name' => $item['category_2'],
                'parent' => $category1['external_id'] ?? null,
                'external_id' => join('-', [$category1['external_id'] ?? null, $item['category_2']]),
                'depth' => 1,
            ];
            //parent::import(CategoryDto::fromArray($category2)->toArray());
        }
        
        if ($item['category_3'] ?? false) {
            $category3 = [
                'name' => $item['category_3'],
                'parent' => $category2['external_id'] ?? null,
                'external_id' => join('-', [$category2['external_id'] ?? null, $item['category_3']]),
                'depth' => 2,
            ];
            //parent::import(CategoryDto::fromArray($category3)->toArray(), ImporterResourcesEnum::CATEGORIES);
        }
    }
}