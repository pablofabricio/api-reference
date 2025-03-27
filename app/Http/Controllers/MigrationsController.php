<?php

namespace App\Http\Controllers;

use App\Jobs\ShopDuplication\BrandJob;
use App\Jobs\ShopImportation\ProductImportJob;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class MigrationsController extends Controller
{
    function __construct()
    {
        
    }

    public function duplicateShop(Request $request)
    {
        $requestData = $request->all();
        $requestData['migration_id'] = Str::uuid();

        dispatch(new BrandJob($requestData));
        
        return response('', Response::HTTP_NO_CONTENT);
    }
}
