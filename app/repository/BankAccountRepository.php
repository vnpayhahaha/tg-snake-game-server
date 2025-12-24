<?php

namespace app\repository;

use app\constants\BankAccount;
use app\constants\Channel;
use app\model\ModelBankAccount;
use DI\Attribute\Inject;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class BankAccountRepository.
 * @extends IRepository<ModelBankAccount>
 */
final class BankAccountRepository extends IRepository
{
    #[Inject]
    protected ModelBankAccount $model;

    public function handleSearch(Builder $query, array $params): Builder
    {

        if (isset($params['channel_id']) && filled($params['channel_id'])) {
            $query->where('channel_id', $params['channel_id']);
        }

        if (isset($params['branch_name']) && filled($params['branch_name'])) {
            $query->where('branch_name', $params['branch_name']);
        }

        if (isset($params['account_holder']) && filled($params['account_holder'])) {
            $query->where('account_holder', $params['account_holder']);
        }

        if (isset($params['account_number']) && filled($params['account_number'])) {
            $query->where('account_number', $params['account_number']);
        }

        if (isset($params['bank_code']) && filled($params['bank_code'])) {
            $query->where('bank_code', $params['bank_code']);
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

    // 查询验证是否具有等级匹配的银行卡
    public function checkBankCardOfCollection(int $safe_level, float $order_amount): bool
    {
        // 更新 当前日期使用次数 和 金额
        $this->updateBankCardUseTimesAndAmount();
        return $this->getBankCardOfCollectionQuery($safe_level, $order_amount)->count() > 0;
    }

    // 更新 当前日期使用次数 和 金额
    public function updateBankCardUseTimesAndAmount(): bool
    {
        // stat_date 为空或者小于 当前日期，则更新stat_date、today_receipt_count、today_payment_count、today_receipt_amount、today_payment_amount
        $updateNum = $this->getModel()->whereNull('stat_date')
            ->orWhere('stat_date', '<', date('Y-m-d'))
            ->update([
                'stat_date'            => date('Y-m-d'),
                'today_receipt_count'  => 0,
                'today_payment_count'  => 0,
                'today_receipt_amount' => 0,
                'today_payment_amount' => 0,
            ]);
        return $updateNum > 0;
    }

    // 查询 等级匹配且金额在单笔最小和最大之间的银行卡
    public function getBankCardOfCollectionQuery(int $safe_level, float $order_amount): Builder
    {
        $query = $this->model::query();
        //-- 当日已收款金额 today_receipt_amount,当日已收款次数 today_receipt_count,used_quota 使用额度, limit_quota
        $rawWhere = '(today_receipt_amount + ? <= daily_max_receipt OR daily_max_receipt = 0) AND (today_receipt_count < daily_max_receipt_count OR daily_max_receipt_count = 0)';
        $rawWhere .= ' AND (limit_quota = 0 OR (limit_quota > 0 AND used_quota + ? <= limit_quota))';
        // 获取等级匹配的银行卡
        return $query
            ->where('security_level', $safe_level)
            ->where('status', BankAccount::STATUS_ENABLE)
            ->where('support_collection', BankAccount::SUPPORT_COLLECTION_YES)
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
                $query->where('status', Channel::STATUS_ENABLE)
                    ->where('support_collection', BankAccount::SUPPORT_COLLECTION_YES);
            });
    }
}
