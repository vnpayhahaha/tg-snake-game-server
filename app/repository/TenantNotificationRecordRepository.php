<?php

namespace app\repository;

use app\model\ModelTenantNotificationRecord;
use DI\Attribute\Inject;
use Illuminate\Database\Eloquent\Builder;

class TenantNotificationRecordRepository extends IRepository
{
    #[Inject]
    protected ModelTenantNotificationRecord $model;

    public function handleSearch(Builder $query, array $params): Builder
    {
        if (isset($params['tenant_id']) && filled($params['tenant_id'])) {
            $query->where('tenant_id', $params['tenant_id']);
        }
        if (isset($params['app_id']) && filled($params['app_id'])) {
            $query->where('app_id', $params['app_id']);
        }
        if (isset($params['account_type']) && filled($params['account_type'])) {
            $query->where('account_type', $params['account_type']);
        }
        if (isset($params['disbursement_order_id']) && filled($params['disbursement_order_id'])) {
            $query->where('disbursement_order_id', $params['disbursement_order_id']);
        }
        if (isset($params['notification_type']) && filled($params['notification_type'])) {
            $query->where('notification_type', $params['notification_type']);
        }
        if (isset($params['response_status']) && filled($params['response_status'])) {
            $query->where('response_status', $params['response_status']);
        }
        if (isset($params['execute_count']) && filled($params['execute_count'])) {
            $query->where('execute_count', $params['execute_count']);
        }
        if (isset($params['status']) && filled($params['status'])) {
            $query->where('status', $params['status']);
        }

        return $query;
    }

    public function page(array $params = [], ?int $page = null, ?int $pageSize = null): array
    {
        $result = $this->perQuery($this->getQuery(), $params)
            ->with('tenant:tenant_id,company_name')
            ->with('app:id,app_name,app_key')
            ->with('collection_order:id,platform_order_no,tenant_order_no')
            ->with('disbursement_order:id,platform_order_no,tenant_order_no')
            ->paginate(
                perPage: $pageSize,
                pageName: static::PER_PAGE_PARAM_NAME,
                page: $page,
            );
        return $this->handlePage($result);
    }
}