<?php

namespace app\repository;

use app\model\ModelTenant;
use DI\Attribute\Inject;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class TenantRepository.
 * @extends IRepository<ModelTenant>
 */
class TenantRepository extends IRepository
{
    #[Inject]
    protected ModelTenant $model;

    public function handleSearch(Builder $query, array $params): Builder
    {

        if (isset($params['tenant_id']) && filled($params['tenant_id'])) {
            $query->where('tenant_id', $params['tenant_id']);
        }

        if (isset($params['contact_user_name']) && filled($params['contact_user_name'])) {
            $query->where('contact_user_name', $params['contact_user_name']);
        }

        if (isset($params['contact_phone']) && filled($params['contact_phone'])) {
            $query->where('contact_phone', $params['contact_phone']);
        }

        if (isset($params['company_name']) && filled($params['company_name'])) {
            $query->where('company_name', $params['company_name']);
        }

        if (isset($params['user_num_limit']) && filled($params['user_num_limit'])) {
            $query->where('user_num_limit', $params['user_num_limit']);
        }

        if (isset($params['is_enabled']) && filled($params['is_enabled'])) {
            $query->where('is_enabled', $params['is_enabled']);
        }

        if (isset($params['created_by']) && filled($params['created_by'])) {
            $query->where('created_by', $params['created_by']);
        }

        if (isset($params['safe_level']) && filled($params['safe_level'])) {
            $query->where('safe_level', $params['safe_level']);
        }

        if (isset($params['tg_chat_id']) && filled($params['tg_chat_id'])) {
            $query->where('tg_chat_id', $params['tg_chat_id']);
        }

        if (isset($params['cashier_template']) && filled($params['cashier_template'])) {
            $query->where('cashier_template', $params['cashier_template']);
        }

        return $query;
    }

    public function getTenantByTgChatId(int $tg_chat_id): ?ModelTenant
    {
        return $this->model::query()
            ->with('accounts')
            ->where('tg_chat_id', $tg_chat_id)
            ->first();
    }

    public function getTenantByTenantId(int $tenant_id): ?ModelTenant
    {
        return $this->model::query()->where('tenant_id', $tenant_id)->first();
    }

    // 获取租户ID by CreatedBy
    public function getTenantIdsByCreatedBy(int $created_by): array
    {
        return $this->model::query()->where('created_by', $created_by)->pluck('tenant_id')->toArray();
    }
}
