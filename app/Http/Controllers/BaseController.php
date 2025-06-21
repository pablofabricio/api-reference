<?php

namespace App\Http\Controllers;

use App\Contracts\ControllerInterface;
use App\Services\BaseService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Routing\Controller;
use Symfony\Component\HttpFoundation\Response;

class BaseController extends Controller implements ControllerInterface
{
    /**
     * @var BaseService
     */
    protected BaseService $service;

    /**
     * @var JsonResource|string
     */
    protected string $resource;

    /**
     * @param BaseService $service
     * @param string $resource
     */
    public function __construct(BaseService $service, string $resource)
    {
        $this->service = $service;
        $this->resource = $resource;
    }

    /**
     * Lista paginada de registros.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index()
    {
        return $this->resource::collection($this->service->getPaginate());
    }

    /**
     * Cria um novo registro.
     */
    public function store(Request $request)
    {
        $model = $this->service->create($request->all());
        return new $this->resource($model);
    }

    /**
     * Exibe um registro específico.
     */
    public function show($id)
    {
        $model = $this->service->find($id);
        return new $this->resource($model);
    }

    /**
     * Atualiza um registro.
     */
    public function update(Request $request, $id)
    {
        $model = $this->service->update($id, $request->all());
        return new $this->resource($model);
    }

    /**
     * Deleta um registro.
     */
    public function destroy($id)
    {
        $this->service->delete($id);
        return response('', Response::HTTP_NO_CONTENT);
    }
}
