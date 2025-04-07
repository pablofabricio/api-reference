<?php

namespace App\Services\ShopImportation\Importers;

use App\Jobs\ShopImportation\ShopImportItem;
use App\Jobs\ShopImportation\ShopImportJob;
use App\Repositories\ShopImportationRepository;
use App\Services\ApiPlusRequestService;
use App\Services\ShopImportation\DTO\ShopImportationDto;
use App\Services\ShopImportation\Enums\EventsStatusEnum;
use App\Services\ShopImportation\Events\ShopImportationEvent;
use App\Services\ShopImportation\Importers\ShopImporterInterface;
use App\Services\ShopImportation\ShopImportationLogger;
use Illuminate\Support\Facades\Log;

class ShopImporterService implements ShopImporterInterface 
{
    private ShopImportationRepository $shopImportationRepository;
    private ShopImportationLogger $logger;

    function __construct(
        ShopImportationRepository $shopImportationRepository,
        ShopImportationLogger $logger
    )
    {
        $this->shopImportationRepository = $shopImportationRepository;
        $this->logger = $logger;
    }

    public function process(ShopImportationEvent $event): void
    {
        if ($event->getStatus() == EventsStatusEnum::FINISHED) {
            return;
        }
    
        $records = $event->generateRecords($event->getCurrentIndex() ?? 0);

        if (!$records->valid()) {
            $event->setStatus(EventsStatusEnum::FINISHED);
            $this->logger->updateStatus($event->getRequestId(), $event->getStatus());
            return;
        }
    
        $event->setItem($records->current());
        $event->setCurrentIndex($event->getCurrentIndex() + 1);
        
        dispatch(new ShopImportItem($event))->onQueue('shop-importation');
        dispatch(new ShopImportJob($event))->onQueue('shop-importation');
    }

    function import(ShopImportationEvent $event): void
    {
        $item = $event->getItem();

        if ($this->shopImportationRepository->find($event->getResource(), $item['external_id'])) {
            return;
        }

        try {
            $api = new ApiPlusRequestService($event->getToken()); 
            $response = $api->post($event->getResource(), $item); 

            $item['resource'] = $event->getResource();
            $item['to_id'] = $response->id;
            $item['token'] = $event->getToken();
            $item['request_id'] = $event->getRequestId();
            $data = ShopImportationDto::fromArray($item)->toArray(); 
            
            $this->shopImportationRepository->insert($data);
        } catch (\Throwable $th) {
            $this->logger->addError($event->getRequestId(), [
                'message' => $th->getMessage(),
                'item' => $event->getItem(),
                'resource' => $event->getResource(),
            ]);
        }
    }
}