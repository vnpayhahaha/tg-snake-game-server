<?php

namespace app\repository;

use app\model\ModelRecycleBin;
use DI\Attribute\Inject;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class RecycleBinRepository.
 * @extends IRepository<ModelRecycleBin>
 */
final class RecycleBinRepository extends IRepository
{
    #[Inject]
    protected ModelRecycleBin $model;

    public function handleSearch(Builder $query, array $params): Builder
    {

        if (isset($params['tenant_id']) && filled($params['tenant_id'])) {
            $query->where('tenant_id', $params['tenant_id']);
        }

        if (isset($params['enabled']) && filled($params['enabled'])) {
            $query->where('enabled', $params['enabled']);
        }

        if (isset($params['operate_by']) && filled($params['operate_by'])) {
            $query->where('operate_by', $params['operate_by']);
        }

        if (isset($params['created_at']) && filled($params['created_at'])) {
            $query->where('created_at', $params['created_at']);
        }

        return $query;
    }
}
