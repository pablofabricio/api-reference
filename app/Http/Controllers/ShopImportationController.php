<?php

namespace App\Http\Controllers;

use App\Services\ShopImportation\ShopImportationService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

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

        $this->service->process($request); 

        return response()->json('', Response::HTTP_NO_CONTENT);
    }
}
