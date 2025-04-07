<?php

namespace App\Http\Controllers;

use App\Services\ShopImportation\ShopImportationService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ShopImportationController extends Controller
{
    private ShopImportationService $service; 

    function __construct(ShopImportationService $shopImportationService)
    {
        $this->service = $shopImportationService;
    }

    public function process(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'resource' => 'required|string',
            'file' => 'required|file',
        ]);

        $data = $this->service->process($request); 

        return response()->json($data, Response::HTTP_CREATED);
    }

    public function getStatusByRequestId(string $requestId)
    {
        $data = $this->service->getStatusByRequestId($requestId);

        return response()->json($data, Response::HTTP_OK);
    }
}
