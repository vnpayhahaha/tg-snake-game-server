<?php

namespace app\repository;

use app\model\ModelTenantNotificationQueue;
use DI\Attribute\Inject;
use Illuminate\Database\Eloquent\Builder;

class TenantNotificationQueueRepository extends IRepository
{
    #[Inject]
    protected ModelTenantNotificationQueue $model;

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
        if (isset($params['collection_order_id']) && filled($params['collection_order_id'])) {
            $query->where('collection_order_id', $params['collection_order_id']);
        }
        if (isset($params['disbursement_order_id']) && filled($params['disbursement_order_id'])) {
            $query->where('disbursement_order_id', $params['disbursement_order_id']);
        }
        if (isset($params['notification_type']) && filled($params['notification_type'])) {
            $query->where('notification_type', $params['notification_type']);
        }
        if (isset($params['notification_url']) && filled($params['notification_url'])) {
            $query->where('notification_url', $params['notification_url']);
        }
        if (isset($params['request_method']) && filled($params['request_method'])) {
            $query->where('request_method', $params['request_method']);
        }
        if (isset($params['request_data']) && filled($params['request_data'])) {
            $query->where('request_data', $params['request_data']);
        }
        if (isset($params['execute_status']) && filled($params['execute_status'])) {
            $query->where('execute_status', $params['execute_status']);
        }
        if (isset($params['next_execute_time']) && filled($params['next_execute_time'])) {
            $query->where('next_execute_time', $params['next_execute_time']);
        }
        if (isset($params['last_execute_time']) && filled($params['last_execute_time'])) {
            $query->where('last_execute_time', $params['last_execute_time']);
        }
        if (isset($params['error_message']) && filled($params['error_message'])) {
            $query->where('error_message', $params['error_message']);
        }
        if (isset($params['created_at']) && filled($params['created_at'])) {
            $query->where('created_at', $params['created_at']);
        }
        if (isset($params['updated_at']) && filled($params['updated_at'])) {
            $query->where('updated_at', $params['updated_at']);
        }

        return $query;
    }

    public function page(array $params = [], ?int $page = null, ?int $pageSize = null): array
    {
        $result = $this->perQuery($this->getQuery(), $params)
            ->with('records')
            ->paginate(
                perPage: $pageSize,
                pageName: static::PER_PAGE_PARAM_NAME,
                page: $page,
            );
        return $this->handlePage($result);
    }
}
