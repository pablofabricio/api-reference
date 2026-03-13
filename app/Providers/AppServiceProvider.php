<?php

namespace App\Providers;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Cache for model table ownership checks.
     *
     * @var array<string, bool>
     */
    private array $ownershipTableCache = [];

    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        BaseModel::addGlobalScope('owned_by_auth', function (Builder $builder): void {
            if (! $this->shouldApplyOwnershipScope($builder->getModel())) {
                return;
            }

            $builder->where($builder->getModel()->getTable() . '.user_id', Auth::id());
        });

        BaseModel::creating(function (BaseModel $model): void {
            if (! $this->shouldFillOwnedUserId($model)) {
                return;
            }

            if (empty($model->user_id)) {
                $model->user_id = Auth::id();
            }
        });
    }

    private function shouldApplyOwnershipScope(BaseModel $model): bool
    {
        return Auth::check() && $this->modelUsesOwnershipColumn($model);
    }

    private function shouldFillOwnedUserId(BaseModel $model): bool
    {
        return Auth::check() && $this->modelUsesOwnershipColumn($model);
    }

    private function modelUsesOwnershipColumn(BaseModel $model): bool
    {
        $table = $model->getTable();

        if (array_key_exists($table, $this->ownershipTableCache)) {
            return $this->ownershipTableCache[$table];
        }

        try {
            $usesOwnership = Schema::hasTable($table) && Schema::hasColumn($table, 'user_id');
        } catch (\Throwable $e) {
            $usesOwnership = false;
        }

        $this->ownershipTableCache[$table] = $usesOwnership;

        return $usesOwnership;
    }
}
