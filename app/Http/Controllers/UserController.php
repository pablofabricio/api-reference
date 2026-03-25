<?php

namespace App\Http\Controllers;

use App\Services\UserService;
use App\Http\Resources\UserResource;
use App\Http\Resources\UserProfileResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends BaseController
{
    protected UserService $userService;

    public function __construct(UserService $service)
    {
        parent::__construct($service, UserResource::class);
        $this->userService = $service;
    }

    public function profile(int $id)
    {
        $data = $this->userService->getProfile($id, Auth::id());

        return new UserProfileResource($data);
    }

    public function updateProfile(Request $request, int $id)
    {
        $user = $this->userService->updateProfile($id, Auth::id(), $request->all());

        return new UserResource($user);
    }
}
