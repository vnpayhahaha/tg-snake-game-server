<?php

namespace app\repository;

use app\model\ModelPosition;
use DI\Attribute\Inject;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class PositionRepository.
 * @extends IRepository<ModelPosition>
 */
final class PositionRepository extends IRepository
{
    #[Inject]
    protected ModelPosition $model;

    public function handleSearch(Builder $query, array $params): Builder
    {
        return $query
            ->when(isset($params['name']), static function (Builder $query) use ($params) {
                $query->where('name', 'like', '%' . $params['name'] . '%');
            })
            ->when(isset($params['dept_id']), static function (Builder $query) use ($params) {
                $query->where('dept_id', $params['dept_id']);
            })
            ->when(isset($params['created_at']), static function (Builder $query) use ($params) {
                $query->whereBetween('created_at', $params['created_at']);
            })
            ->when(isset($params['updated_at']), static function (Builder $query) use ($params) {
                $query->whereBetween('updated_at', $params['updated_at']);
            })
            ->with(['department', 'policy']);
    }
}
