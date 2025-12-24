<?php

namespace app\repository;

use app\model\ModelTenantApiInterface;
use DI\Attribute\Inject;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class TenantApiInterfaceRepository.
 * @extends IRepository<ModelTenantApiInterface>
 */
class TenantApiInterfaceRepository extends IRepository
{
    #[Inject]
    protected ModelTenantApiInterface $model;

    public function handleSearch(Builder $query, array $params): Builder
    {

        if (isset($params['api_name']) && filled($params['api_name'])) {
            $query->where('api_name', $params['api_name']);
        }

        if (isset($params['api_uri']) && filled($params['api_uri'])) {
            $query->where('api_uri', $params['api_uri']);
        }

        if (isset($params['http_method']) && filled($params['http_method'])) {
            $query->where('http_method', $params['http_method']);
        }

        return $query;
    }
}
