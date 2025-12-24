<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

abstract class BaseRepository
{
    /**
     * The model instance.
     *
     * @var Model
     */
    protected Model $model;

    /**
     * Constructor to bind model to repo.
     */
    public function __construct()
    {
        $this->model = $this->getModelInstance();
    }

    /**
     * Return the model class name.
     *
     * @return string
     */
    abstract protected function model(): string;

    /**
     * Instantiate model.
     *
     * @return Model
     */
    protected function getModelInstance(): Model
    {
        return app($this->model());
    }

    /**
     * Public accessor for the model instance used by the repository.
     *
     * @return Model
     */
    public function getModel(): Model
    {
        return $this->model;
    }

    /**
     * Pagina os registros com o valor padrão do modelo.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getPaginate()
    {
        return $this->model->paginate();
    }

    /**
     * Find record by ID.
     *
     * @param  int  $id
     * @return Model|null
     */
    public function find(int $id): ?Model
    {
        return $this->model->find($id);
    }

    /**
     * Create a new record.
     *
     * @param  array  $data
     * @return Model
     */
    public function create(array $data): Model
    {
        return $this->model->create($data);
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
        $record = $this->find($id);
        if ($record) {
            $record->update($data);
        }
        return $record;
    }

    /**
     * Delete a record.
     *
     * @param  int  $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        $record = $this->find($id);
        return $record ? $record->delete() : false;
    }
}
