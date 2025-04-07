<?php

namespace App\Services\ShopImportation;

use App\Jobs\ShopImportation\ShopImportJob;
use App\Models\ShopImportationLog;
use App\Repositories\ShopImportationLogRepository;
use App\Services\ShopImportation\Enums\EventsStatusEnum;
use App\Services\ShopImportation\Factories\EventsFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ShopImportationService 
{
    private ShopImportationLogRepository $shopImportationLogRepository;

    public function __construct(
        ShopImportationLogRepository $shopImportationLogRepository,
    )
    {
        $this->shopImportationLogRepository = $shopImportationLogRepository;
    }

    public function process(Request $request): ShopImportationLog
    {
        $requestData = $request->except('file');
        $requestData['request_id'] = Str::uuid()->toString();
       
        $file = $request->file('file');
        $requestData['filePath'] = 'shop-importations/'. $requestData['request_id'] . '.' . $file->getClientOriginalExtension();
        Storage::disk('local')->put($requestData['filePath'], file_get_contents($file));

        $event = EventsFactory::getInstance($request['resource'], $file->getClientOriginalExtension());
        $event->setRequest($requestData);

        dispatch(new ShopImportJob($event))->onQueue('shop-importation'); 
        
        return $this->shopImportationLogRepository->insert([
            'request_id' => $requestData['request_id'],
            'status' => EventsStatusEnum::ON_QUEUE,
            'errors' => null,
        ]);
    }

    public function getStatusByRequestId(string $requestId): ?ShopImportationLog
    {
        return $this->shopImportationLogRepository->findByRequestId($requestId);
    }
}