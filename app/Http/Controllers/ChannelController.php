<?php

namespace App\Http\Controllers;

use App\Services\ChannelService;
use App\Http\Resources\ChannelResource;
use Illuminate\Http\Request;

class ChannelController extends BaseController
{
    public function __construct(ChannelService $service)
    {
        parent::__construct($service, ChannelResource::class);
    }

    public function index()
    {
        $paginator = $this->service->getPaginateForUser();
        return ChannelResource::collection($paginator);
    }

    public function withReferences(Request $request, $id)
    {
        $channel = $this->service->find($id);
        $channel->load(['references.nodes']);
        return new ChannelResource($channel);
    }
}
