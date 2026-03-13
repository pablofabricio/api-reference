<?php

namespace App\Services;

use App\Repositories\BaseRepository;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Model;
use ReflectionClass;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

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
        return $this->repository->getPaginate($this->paginateConstraints());
    }

    /**
     * Find record by ID.
     *
     * @param  int  $id
     * @return Model|null
     */
    public function find(int $id): ?Model
    {
        $model = $this->repository->find($id);

        if (! $model && $this->enforcesUserOwnership() && $this->repository->findWithoutGlobalScopes($id)) {
            throw new AuthorizationException('Unauthorized');
        }

        if ($model) {
            $this->authorizeModelAccess($model);
        }

        return $model;
    }

    /**
     * Create a new record.
     *
     * @param  array  $data
     * @return Model
     */
    public function create(array $data): Model
    {
        $model = $this->repository->getModel();
        $data = $this->withAuthenticatedUserId($model, $data);
        $rules = $this->resolveValidationRules($model);

        if (!empty($rules)) {
            $v = Validator::make($data, $rules);
            if ($v->fails()) {
                throw new ValidationException($v);
            }
        }

        $this->authorizePayload($data);

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
        $record = $this->find($id);
        if (! $record) {
            return null;
        }

        $model = $this->repository->getModel();
        $rules = $this->resolveValidationRules($model);

        if (!empty($rules)) {
            $v = Validator::make($data, $rules);
            if ($v->fails()) {
                throw new ValidationException($v);
            }
        }

        $this->authorizePayload($data);

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
        $record = $this->find($id);
        if (! $record) {
            return false;
        }

        return $this->repository->delete($id);
    }

    /**
     * Override to constrain index() queries by access scope.
     */
    protected function paginateConstraints(): array
    {
        return [];
    }

    /**
     * Override to enforce payload-level authorization (create/update).
     */
    protected function authorizePayload(array $data): void
    {
        if (! $this->enforcesUserOwnership()) {
            return;
        }

        if (! array_key_exists('user_id', $data)) {
            return;
        }

        if ((int) $data['user_id'] !== (int) Auth::id()) {
            throw new AuthorizationException('Unauthorized');
        }
    }

    /**
     * Override to enforce record-level authorization (show/update/delete).
     */
    protected function authorizeModelAccess(Model $model): void
    {
        if (! $this->enforcesUserOwnership()) {
            return;
        }

        if ((int) $model->getAttribute('user_id') !== (int) Auth::id()) {
            throw new AuthorizationException('Unauthorized');
        }
    }

    protected function enforcesUserOwnership(): bool
    {
        return true;
    }

    private function resolveValidationRules(Model $model): array
    {
        if (method_exists($model, 'rules')) {
            return $model::rules();
        }

        $defaults = (new ReflectionClass($model))->getDefaultProperties();
        $rules = $defaults['rules'] ?? [];

        return is_array($rules) ? $rules : [];
    }

    private function withAuthenticatedUserId(Model $model, array $data): array
    {
        if (! $this->enforcesUserOwnership() || ! Auth::check()) {
            return $data;
        }

        if (array_key_exists('user_id', $data)) {
            return $data;
        }

        $fillable = $model->getFillable();
        if (! in_array('user_id', $fillable, true)) {
            return $data;
        }

        $data['user_id'] = (int) Auth::id();

        return $data;
    }
}
