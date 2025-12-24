<?php

namespace App\Http\Controllers;

use App\Services\UserService;
use App\Http\Resources\UserResource;

class UserController extends BaseController
{
    public function __construct(UserService $service)
    {
        parent::__construct($service, UserResource::class);
    }
}
