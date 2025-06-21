<?php

namespace App\Services;

use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class BaseService
{
    /**
     * @var BaseRepository
     */
    protected BaseRepository $repository;

    /**
     * @var string
     */
    protected string $serviceClass;

    /**
     * @param BaseRepository $repository
     */
    public function __construct(BaseRepository $repository)
    {
        $this->repository = $repository;
        $this->serviceClass = get_called_class();
    }

    /**
     * Retorna registros paginados com base no padrão do model.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getPaginate()
    {
        return $this->repository->getPaginate();
    }

    /**
     * Find record by ID.
     *
     * @param  int  $id
     * @return Model|null
     */
    public function find(int $id): ?Model
    {
        return $this->repository->find($id);
    }

    /**
     * Create a new record.
     *
     * @param  array  $data
     * @return Model
     */
    public function create(array $data): Model
    {
        return $this->repository->create($data);
    }

    /**
     * Update a record.
     *
     * @param  int    $id
     * @param  array  $data
     * @return Model|null
     */
    public function update(int $id, array $data): ?Model
    {
        return $this->repository->update($id, $data);
    }

    /**
     * Delete a record.
     *
     * @param  int  $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        return $this->repository->delete($id);
    }
}
