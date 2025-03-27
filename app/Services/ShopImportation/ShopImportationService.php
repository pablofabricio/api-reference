<?php

namespace App\Services\ShopImportation;

use App\Jobs\ShopImportation\ShopImportJob;
use App\Services\ShopImportation\Factories\EventsFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ShopImportationService 
{
    public function process(Request $request): void
    {
        $requestData = $request->except('file');
        $requestData['request_id'] = Str::uuid();
       
        $file = $request->file('file');
        $requestData['filePath'] = 'shop-importations/'. $requestData['request_id'] . '.' . $file->getClientOriginalExtension();
        Storage::disk('local')->put($requestData['filePath'], file_get_contents($file));

        $event = EventsFactory::getInstance($request['resource'], $file->getClientOriginalExtension());
        $event->setRequest($requestData);
        
        dispatch(new ShopImportJob($event))->onQueue('shop-importation'); 
    }
}