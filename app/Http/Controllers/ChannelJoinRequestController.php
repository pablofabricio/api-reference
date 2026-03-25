<?php

namespace App\Http\Controllers;

use App\Http\Resources\ChannelJoinRequestResource;
use App\Services\ChannelJoinRequestService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Symfony\Component\HttpFoundation\Response;

class ChannelJoinRequestController extends Controller
{
    public function __construct(private readonly ChannelJoinRequestService $service)
    {
    }

    public function index(Request $request)
    {
        return ChannelJoinRequestResource::collection(
            $this->service->listForAuthenticatedUser($request->query('status'))
        );
    }

    public function store(int $id)
    {
        return (new ChannelJoinRequestResource($this->service->createForChannel($id)))
            ->response()
            ->setStatusCode(201);
    }

    public function update(Request $request, int $id)
    {
        return new ChannelJoinRequestResource($this->service->updateStatus($id, $request->all()));
    }

    public function destroy(int $id)
    {
        $this->service->cancel($id);

        return response('', Response::HTTP_NO_CONTENT);
    }
}