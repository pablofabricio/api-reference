<?php

namespace App\Contracts;

use Illuminate\Http\Request;

interface ControllerInterface
{
    /**
     * Lista os recursos paginados.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index();

    /**
     * Armazena um novo recurso.
     *
     * @param Request $request
     * @return mixed
     */
    public function store(Request $request);

    /**
     * Exibe um recurso específico.
     *
     * @param int|string $id
     * @return mixed
     */
    public function show($id);

    /**
     * Atualiza um recurso específico.
     *
     * @param Request $request
     * @param int|string $id
     * @return mixed
     */
    public function update(Request $request, $id);

    /**
     * Remove um recurso específico.
     *
     * @param int|string $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id);
}
