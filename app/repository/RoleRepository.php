<?php

namespace app\repository;

use app\model\ModelRole;
use DI\Attribute\Inject;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;

/**
 * Class RoleRepository.
 * @extends IRepository<ModelRole>
 */
final class RoleRepository extends IRepository
{
    #[Inject]
    protected ModelRole $model;

    public function handleSearch(Builder $query, array $params): Builder
    {
        return $query
            ->when(Arr::get($params, 'name'), static function (Builder $query, $name) {
                $query->where('name', 'like', '%' . $name . '%');
            })
            ->when(Arr::get($params, 'code'), static function (Builder $query, $code) {
                $query->whereIn('code', Arr::wrap($code));
            })
            ->when(Arr::has($params, 'status'), static function (Builder $query) use ($params) {
                $query->where('status', $params['status']);
            })
            ->when(Arr::get($params, 'created_at'), static function (Builder $query, $createdAt) {
                $query->whereBetween('created_at', $createdAt);
            });
    }
}
