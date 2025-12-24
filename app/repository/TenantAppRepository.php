<?php

namespace app\repository;

use app\model\ModelTenantApp;
use DI\Attribute\Inject;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class TenantAppRepository.
 * @extends IRepository<ModelTenantApp>
 */
class TenantAppRepository extends IRepository
{
    #[Inject]
    protected ModelTenantApp $model;

    public function handleSearch(Builder $query, array $params): Builder
    {

        if (isset($params['tenant_id']) && filled($params['tenant_id'])) {
            $query->where('tenant_id', $params['tenant_id']);
        }

        if (isset($params['app_name']) && filled($params['app_name'])) {
            $query->where('app_name', $params['app_name']);
        }

        if (isset($params['app_key']) && filled($params['app_key'])) {
            $query->where('app_key', $params['app_key']);
        }

        if (isset($params['status']) && filled($params['status'])) {
            $query->where('status', $params['status']);
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

    // 获取 AppSecret by app_key
    public function getAppSecretByAppKey(string $app_key): string|null
    {
        return $this->getQuery()->where('app_key', $app_key)->value('app_secret');
    }
}
