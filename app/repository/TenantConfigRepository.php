<?php

namespace app\repository;

use app\model\ModelTenantConfig;
use DI\Attribute\Inject;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class TenantConfigRepository.
 * @extends IRepository<ModelTenantConfig>
 */
class TenantConfigRepository extends IRepository
{
    #[Inject]
    protected ModelTenantConfig $model;

    public function handleSearch(Builder $query, array $params): Builder
    {

        if (isset($params['tenant_id']) && filled($params['tenant_id'])) {
            $query->where('tenant_id', $params['tenant_id']);
        }

        if (isset($params['group_code']) && filled($params['group_code'])) {
            $query->where('group_code', $params['group_code']);
        }

        if (isset($params['code']) && filled($params['code'])) {
            $query->where('code', $params['code']);
        }

        if (isset($params['name']) && filled($params['name'])) {
            $query->where('name', $params['name']);
        }

        if (isset($params['enabled']) && filled($params['enabled'])) {
            $query->where('enabled', $params['enabled']);
        }

        return $query;
    }

    public function page(array $params = [], ?int $page = null, ?int $pageSize = null): array
    {
        $result = $this->perQuery($this->getQuery(), $params)->with('tenant:tenant_id,company_name')->paginate(
            perPage: $pageSize,
            pageName: static::PER_PAGE_PARAM_NAME,
            page: $page,
        );
        return $this->handlePage($result);
    }
}
