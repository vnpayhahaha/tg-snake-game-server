<?php

namespace app\repository\Traits;


use Illuminate\Database\Query\Builder;

trait RepositoryOrderByTrait
{
    public function handleOrderBy(Builder $query, $params): Builder
    {
        if ($this->enablePageOrderBy()) {
            $orderByField = $params[$this->getOrderByParamName()] ?? $query->getModel()->getKeyName();
            $orderByDirection = $params[$this->getOrderByDirectionParamName()] ?? 'desc';
            $query->orderBy($orderByField, $orderByDirection);
        }
        return $query;
    }

    protected function bootRepositoryOrderByTrait(Builder $query, array $params): void
    {
        $this->handleOrderBy($query, $params);
    }

    protected function getOrderByParamName(): string
    {
        return 'order_by';
    }

    protected function getOrderByDirectionParamName(): string
    {
        return 'order_by_direction';
    }

    protected function enablePageOrderBy(): bool
    {
        return true;
    }
}
