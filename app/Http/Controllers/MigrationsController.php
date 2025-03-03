<?php

namespace App\Http\Controllers;

use App\Jobs\BrandJob;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class MigrationsController extends Controller
{
    public function duplicateShop(Request $request)
    {
        $requestData = $request->all();
        $requestData['migration_id'] = Str::uuid();

        dispatch(new BrandJob($requestData)); 

        return response('', Response::HTTP_NO_CONTENT);
    }
}
