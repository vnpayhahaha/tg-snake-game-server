<?php

namespace app\repository;

use app\model\ModelTenantUser;
use DI\Attribute\Inject;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class TenantUserRepository.
 * @extends IRepository<ModelTenantUser>
 */
final class TenantUserRepository extends IRepository
{
    #[Inject]
    protected ModelTenantUser $model;
    public function findByUnameType(string $username, string $tenant_id): ModelTenantUser|null
    {
        // @phpstan-ignore-next-line
        return $this->model->newQuery()
            ->where('username', $username)
            ->where('tenant_id', $tenant_id)
            ->first();
    }
    public function handleSearch(Builder $query, array $params): Builder
    {

        if (isset($params['tenant_id']) && filled($params['tenant_id'])) {
            $query->where('tenant_id', $params['tenant_id']);
        }

        if (isset($params['username']) && filled($params['username'])) {
            $query->where('username', $params['username']);
        }

        if (isset($params['phone']) && filled($params['phone'])) {
            $query->where('phone', $params['phone']);
        }

        if (isset($params['status']) && filled($params['status'])) {
            $query->where('status', $params['status']);
        }

        if (isset($params['is_enabled_google']) && filled($params['is_enabled_google'])) {
            $query->where('is_enabled_google', $params['is_enabled_google']);
        }

        if (isset($params['remark']) && filled($params['remark'])) {
            $query->where('remark', $params['remark']);
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
