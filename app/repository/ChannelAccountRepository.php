<?php

namespace app\repository;

use app\model\ModelChannelAccount;
use DI\Attribute\Inject;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class ChannelAccountRepository.
 * @extends IRepository<ModelChannelAccount>
 */
final class ChannelAccountRepository extends IRepository
{
    #[Inject]
    protected ModelChannelAccount $model;

    public function handleSearch(Builder $query, array $params): Builder
    {

        if (isset($params['channel_id']) && filled($params['channel_id'])) {
            $query->where('channel_id', $params['channel_id']);
        }

        if (isset($params['merchant_id']) && filled($params['merchant_id'])) {
            $query->where('merchant_id', $params['merchant_id']);
        }

        if (isset($params['limit_quota']) && filled($params['limit_quota'])) {
            $query->where('limit_quota', $params['limit_quota']);
        }

        if (isset($params['status']) && filled($params['status'])) {
            $query->where('status', $params['status']);
        }

        if (isset($params['support_collection']) && filled($params['support_collection'])) {
            $query->where('support_collection', $params['support_collection']);
        }

        if (isset($params['support_disbursement']) && filled($params['support_disbursement'])) {
            $query->where('support_disbursement', $params['support_disbursement']);
        }

        return $query;
    }

    public function page(array $params = [], ?int $page = null, ?int $pageSize = null): array
    {
        $result = $this->perQuery($this->getQuery(), $params)->with('channel:id,channel_name,channel_code,channel_icon')->paginate(
            perPage: $pageSize,
            pageName: static::PER_PAGE_PARAM_NAME,
            page: $page,
        );
        return $this->handlePage($result);
    }

    public function getChannelAccountOfCollectionQuery(int $channel_account_id, float $order_amount): Builder
    {
        $query = $this->model::query();
        //-- 当日已收款金额 today_receipt_amount,当日已收款次数 today_receipt_count,used_quota 使用额度, limit_quota
        $rawWhere = '(today_receipt_amount + ? <= daily_max_receipt OR daily_max_receipt = 0) AND (today_receipt_count < daily_max_receipt_count OR daily_max_receipt_count = 0)';
        $rawWhere .= ' AND (limit_quota = 0 OR (limit_quota > 0 AND used_quota + ? <= limit_quota))';
        // 获取等级匹配的银行卡
        return $query
            ->where('id', $channel_account_id)
            ->where('status', true)
            ->where('support_collection', true)
            ->where(function ($query) use ($order_amount) {
                $query->where(function ($query) use ($order_amount) {
                    $query->where('min_receipt_per_txn', '>', 0)
                        ->where('min_receipt_per_txn', '<=', $order_amount);
                })->orWhere('min_receipt_per_txn', '=', 0);
            })
            ->where(function ($query) use ($order_amount) {
                $query->where(function ($query) use ($order_amount) {
                    $query->where('max_receipt_per_txn', '>', 0)
                        ->where('max_receipt_per_txn', '>=', $order_amount);
                })->orWhere('max_receipt_per_txn', '=', 0);
            })
            ->whereRaw($rawWhere, [$order_amount, $order_amount])
            ->withWhereHas('channel', function ($query) {
                $query->where('status', true)
                    ->where('support_collection', true);
            })->with('channel:id,channel_name,channel_code');
    }
}
