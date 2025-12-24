<?php

namespace app\repository;

use app\constants\CollectionOrder;
use app\model\ModelCollectionOrder;
use DI\Attribute\Inject;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class CollectionOrderRepository.
 * @extends IRepository<ModelCollectionOrder>
 */
final class CollectionOrderRepository extends IRepository
{
    #[Inject]
    protected ModelCollectionOrder $model;

    public function handleSearch(Builder $query, array $params): Builder
    {

        if (isset($params['platform_order_no']) && filled($params['platform_order_no'])) {
            $query->where('platform_order_no', $params['platform_order_no']);
        }

        if (isset($params['tenant_order_no']) && filled($params['tenant_order_no'])) {
            $query->where('tenant_order_no', $params['tenant_order_no']);
        }

        if (isset($params['upstream_order_no']) && filled($params['upstream_order_no'])) {
            $query->where('upstream_order_no', $params['upstream_order_no']);
        }

        if (isset($params['settlement_type']) && filled($params['settlement_type'])) {
            $query->where('settlement_type', $params['settlement_type']);
        }

        if (isset($params['collection_type']) && filled($params['collection_type'])) {
            $query->where('collection_type', $params['collection_type']);
        }

        if (isset($params['collection_channel_id']) && filled($params['collection_channel_id'])) {
            $query->where('collection_channel_id', $params['collection_channel_id']);
        }
        if (isset($params['bank_account_id']) && filled($params['bank_account_id'])) {
            $query->where('bank_account_id', $params['bank_account_id']);
        }
        if (isset($params['channel_account_id']) && filled($params['channel_account_id'])) {
            $query->where('channel_account_id', $params['channel_account_id']);
        }

        if (isset($params['pay_time']) && filled($params['pay_time'])) {
            $query->where('pay_time', $params['pay_time']);
        }

        if (isset($params['expire_time']) && filled($params['expire_time'])) {
            $query->where('expire_time', $params['expire_time']);
        }

        if (isset($params['order_source']) && filled($params['order_source'])) {
            $query->where('order_source', $params['order_source']);
        }

        if (isset($params['recon_type']) && filled($params['recon_type'])) {
            $query->where('recon_type', $params['recon_type']);
        }

        if (isset($params['notify_status']) && filled($params['notify_status'])) {
            $query->where('notify_status', $params['notify_status']);
        }

        if (isset($params['tenant_id']) && filled($params['tenant_id'])) {
            $query->where('tenant_id', $params['tenant_id']);
        }

        if (isset($params['app_id']) && filled($params['app_id'])) {
            $query->where('app_id', $params['app_id']);
        }

        if (isset($params['payer_name']) && filled($params['payer_name'])) {
            $query->where('payer_name', $params['payer_name']);
        }

        if (isset($params['payer_account']) && filled($params['payer_account'])) {
            $query->where('payer_account', $params['payer_account']);
        }

        if (isset($params['payer_bank']) && filled($params['payer_bank'])) {
            $query->where('payer_bank', $params['payer_bank']);
        }

        if (isset($params['payer_ifsc']) && filled($params['payer_ifsc'])) {
            $query->where('payer_ifsc', $params['payer_ifsc']);
        }

        if (isset($params['payer_upi']) && filled($params['payer_upi'])) {
            $query->where('payer_upi', $params['payer_upi']);
        }

        if (isset($params['status']) && filled($params['status'])) {
            if ($params['status'] == 40) {
                $query->where('status', '>=', $params['status']);
            } else {
                $query->where('status', $params['status']);
            }
        }

        if (isset($params['channel_transaction_no']) && filled($params['channel_transaction_no'])) {
            $query->where('channel_transaction_no', $params['channel_transaction_no']);
        }

        if (isset($params['request_id']) && filled($params['request_id'])) {
            $query->where('request_id', $params['request_id']);
        }

        if (isset($params['platform_transaction_no']) && filled($params['platform_transaction_no'])) {
            $query->where('platform_transaction_no', $params['platform_transaction_no']);
        }

        if (isset($params['utr']) && filled($params['utr'])) {
            $query->where('utr', $params['utr']);
        }

        if (isset($params['customer_submitted_utr']) && filled($params['customer_submitted_utr'])) {
            $query->where('customer_submitted_utr', $params['customer_submitted_utr']);
        }

        return $query;
    }

    public function page(array $params = [], ?int $page = null, ?int $pageSize = null): array
    {
        $result = $this->perQuery($this->getQuery(), $params)
            ->with('channel:id,channel_name,channel_code,channel_icon')
            ->with('channel_account:id,merchant_id')
            ->with('bank_account:id,branch_name')
            ->with('cancel_operator:id,username,nickname,avatar')
            ->with('cancel_customer:id,username,avatar')
            ->with('created_customer:id,username,avatar')
            ->with('status_records')
            ->with('settlement_status:id,transaction_no,transaction_status,transaction_type,settlement_delay_mode,settlement_delay_days,expected_settlement_time,failed_msg,remark')
            ->paginate(
                perPage: $pageSize,
                pageName: self::PER_PAGE_PARAM_NAME,
                page: $page,
            );
        $pageData = $this->handlePage($result);
        // 统计数据
        $order_amount = $this->perQuery($this->getQuery(), $params)->sum('amount');
        $payable_amount = $this->perQuery($this->getQuery(), $params)->sum('payable_amount');
        $paid_amount = $this->perQuery($this->getQuery(), $params)->sum('paid_amount');
        return [
            ...$pageData,
            'order_amount'   => $order_amount,
            'payable_amount' => $payable_amount,
            'paid_amount'    => $paid_amount,
        ];
    }

    public function queryCountOrderNum(string $queryWhereSql, string $startTime, string $endTime = null): int
    {
        if ($endTime === null) {
            $endTime = date('Y-m-d H:i:s');
        }
        return $this->getQuery()
            ->whereRaw("1 {$queryWhereSql}")
            ->whereBetween('created_at', [$startTime, $endTime])
            ->count();
    }

    public function queryOrderSuccessfulNum(string $queryWhereSql, string $startTime, string $endTime = null): int
    {
        if ($endTime === null) {
            $endTime = date('Y-m-d H:i:s');
        }
        return $this->getQuery()
            ->whereRaw("1 {$queryWhereSql}")
            ->whereBetween('created_at', [$startTime, $endTime])
            ->where('status', CollectionOrder::STATUS_SUCCESS)
            ->count();
    }
}
