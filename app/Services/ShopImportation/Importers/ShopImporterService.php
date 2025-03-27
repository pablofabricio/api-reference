<?php

namespace App\Services\ShopImportation\Importers;

use App\Jobs\ShopImportation\ShopImportItem;
use App\Jobs\ShopImportation\ShopImportJob;
use App\Repositories\ShopImportationRepository;
use App\Services\ApiPlusRequestService;
use App\Services\ShopImportation\DTO\ShopImportationDto;
use App\Services\ShopImportation\Events\ShopImportationEvent;
use App\Services\ShopImportation\Importers\ShopImporterInterface;
use Illuminate\Support\Facades\Storage;

class ShopImporterService implements ShopImporterInterface 
{
    private ShopImportationRepository $shopImportationRepository;

    function __construct(
        ShopImportationRepository $shopImportationRepository,
    )
    {
        $this->shopImportationRepository = $shopImportationRepository;
    }

    public function process(ShopImportationEvent $event): void
    {
        $records = $event->recordsToArray();
        
        if (empty($records)) {
            return;
        }

        $record = array_shift($records);
        $event->setItem($record); 
        dispatch(new ShopImportItem($event))->onQueue('shop-importation');

        Storage::disk('local')->put($requestData['filePath'], file_get_contents($file));
        
        dispatch(new ShopImportJob($event))->onQueue('shop-importation');
    }

    function import(ShopImportationEvent $event): void
    {
        $item = $event->getItem();

        if ($this->shopImportationRepository->find($event->getResource(), $item['id'])) {
            return;
        }

        $api = new ApiPlusRequestService($event->getToken()); 
        $response = $api->post($event->getResource(), $item); 

        $item['resource'] = $event->getResource();
        $item['to_id'] = $response->id;
        $data = ShopImportationDto::fromArray($item)->toArray(); 

        $this->shopImportationRepository->insert($data);
    }
}