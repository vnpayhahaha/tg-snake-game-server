<?php

namespace app\repository;

use app\model\enums\MenuStatus;
use app\model\ModelMenu;
use DI\Attribute\Inject;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;

/**
 * Class MenuRepository.
 * @extends IRepository<ModelMenu>
 */
final class MenuRepository extends IRepository
{
    #[Inject]
    protected ModelMenu $model;

    public function enablePageOrderBy(): bool
    {
        return false;
    }

    public function list(array $params = []): Collection
    {
        return $this->perQuery($this->getQuery(), $params)->orderBy('sort')->get();
    }

    public function handleSearch(Builder $query, array $params): Builder
    {
        $whereInName = static function (Builder $query, array|string $code) {
            $query->whereIn('name', Arr::wrap($code));
        };
        return $query
            ->when(Arr::get($params, 'sortable'), static function (Builder $query, array $sortable) {
                $query->orderBy(key($sortable), current($sortable));
            })
            ->when(Arr::get($params, 'code'), $whereInName)
            ->when(Arr::get($params, 'name'), $whereInName)
            ->when(Arr::get($params, 'children'), static function (Builder $query) {
                $query->with('children');
            })->when(Arr::get($params, 'status'), static function (Builder $query, MenuStatus $status) {
                $query->where('status', $status);
            })
            ->when(Arr::has($params, 'parent_id'), static function (Builder $query) use ($params) {
                $query->where('parent_id', Arr::get($params, 'parent_id'));
            });
    }

    public function allTree(): Collection
    {
        return $this->model
            ->newQuery()
            ->where('parent_id', 0)
            ->with('children')
            ->get();
    }

    /**
     * 通过 name 查询菜单meta.
     */
    public function findNameByCode(string $name): ?string
    {
        return $this->model::query()->where('name', $name)->value('meta');
    }
}
