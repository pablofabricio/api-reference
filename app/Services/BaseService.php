<?php

namespace App\Services;

use App\Enums\ChannelMemberRole;
use App\Models\Channel;
use App\Models\ChannelMember;
use App\Models\ChannelReference;
use App\Repositories\BaseRepository;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Model;
use ReflectionClass;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
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

    /**
     * OWNER/MODERATOR and channel creator can manage a channel.
     */
    protected function hasChannelManagementAccessByChannelId(int $channelId): bool
    {
        $userId = (int) Auth::id();
        if ($userId <= 0 || $channelId <= 0) {
            return false;
        }

        $ownsChannel = Channel::query()
            ->where('id', $channelId)
            ->where('created_by', $userId)
            ->exists();

        if ($ownsChannel) {
            return true;
        }

        if (! Schema::hasTable('channel_members')) {
            return false;
        }

        return ChannelMember::query()
            ->where('channel_id', $channelId)
            ->where('user_id', $userId)
            ->whereIn('role', [
                ChannelMemberRole::OWNER->value,
                ChannelMemberRole::MODERATOR->value,
            ])
            ->exists();
    }

    /**
     * Only channel creator or an OWNER member can delete a channel.
     */
    protected function hasChannelOwnershipAccessByChannelId(int $channelId): bool
    {
        $userId = (int) Auth::id();
        if ($userId <= 0 || $channelId <= 0) {
            return false;
        }

        $ownsChannel = Channel::query()
            ->where('id', $channelId)
            ->where('created_by', $userId)
            ->exists();

        if ($ownsChannel) {
            return true;
        }

        if (! Schema::hasTable('channel_members')) {
            return false;
        }

        return ChannelMember::query()
            ->where('channel_id', $channelId)
            ->where('user_id', $userId)
            ->where('role', ChannelMemberRole::OWNER->value)
            ->exists();
    }

    /**
     * OWNER/MODERATOR and channel creator can manage nodes/references in a channel.
     */
    protected function hasChannelManagementAccessByReferenceId(int $referenceId): bool
    {
        if ($referenceId <= 0) {
            return false;
        }

        $channelIdsQuery = ChannelReference::query()
            ->select('channel_id')
            ->where('reference_id', $referenceId);

        $userId = (int) Auth::id();
        if ($userId <= 0) {
            return false;
        }

        $ownsAnyChannel = ChannelReference::query()
            ->where('reference_id', $referenceId)
            ->whereHas('channel', fn($q) => $q->where('created_by', $userId))
            ->exists();

        if ($ownsAnyChannel) {
            return true;
        }

        if (! Schema::hasTable('channel_members')) {
            return false;
        }

        return ChannelMember::query()
            ->where('user_id', $userId)
            ->whereIn('role', [
                ChannelMemberRole::OWNER->value,
                ChannelMemberRole::MODERATOR->value,
            ])
            ->whereIn('channel_id', $channelIdsQuery)
            ->exists();
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

}
