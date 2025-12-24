<?php

namespace app\repository;

use app\model\ModelDepartment;
use DI\Attribute\Inject;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;

/**
 * Class DepartmentRepository.
 * @extends IRepository<ModelDepartment>
 */
final class DepartmentRepository extends IRepository
{
    #[Inject]
    protected ModelDepartment $model;

    public function getByIds(array $ids): Collection
    {
        return $this->model->newQuery()->whereIn('id', $ids)->get();
    }

    public function handleSearch(Builder $query, array $params): Builder
    {
        return $query
            ->when(isset($params['id']), static function (Builder $query) use ($params) {
                $query->whereIn('id', Arr::wrap($params['id']));
            })
            ->when(isset($params['name']), static function (Builder $query) use ($params) {
                $query->where('name', 'like', '%' . $params['name'] . '%');
            })
            ->when(isset($params['parent_id']), static function (Builder $query) use ($params) {
                $query->where('parent_id', $params['parent_id']);
            })
            ->when(isset($params['created_at']), static function (Builder $query) use ($params) {
                $query->whereBetween('created_at', $params['created_at']);
            })
            ->when(isset($params['updated_at']), static function (Builder $query) use ($params) {
                $query->whereBetween('updated_at', $params['updated_at']);
            })
            ->when(!empty($params['append_position']), static function (Builder $query) {
                $query->with('positions:id,name');
            })
            ->when(isset($params['level']), static function (Builder $query) use ($params) {
                if ((int)$params['level'] === 1) {
                    $query->where('parent_id', 0);
                    $query->with('children');
                }

                // todo 指定层级查询
            })
            ->with(['positions', 'department_users:id,nickname,username,avatar', 'leader:id,nickname,username,avatar']);
    }
}
