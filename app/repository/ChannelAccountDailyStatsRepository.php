<?php

namespace app\repository;


use app\model\ModelChannelAccountDailyStats;
use DI\Attribute\Inject;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class ChannelAccountDailyStatsRepository.
 * @extends IRepository<ModelChannelAccountDailyStats>
 */
final class ChannelAccountDailyStatsRepository extends IRepository
{
    #[Inject]
    protected ModelChannelAccountDailyStats $model;

    public function handleSearch(Builder $query, array $params): Builder
    {
        if (isset($params['channel_account_id']) && filled($params['channel_account_id'])) {
            $query->where('channel_account_id', $params['channel_account_id']);
        }

        if (isset($params['bank_account_id']) && filled($params['bank_account_id'])) {
            $query->where('bank_account_id', $params['bank_account_id']);
        }

        if (isset($params['channel_id']) && filled($params['channel_id'])) {
            $query->where('channel_id', $params['channel_id']);
        }

        if (isset($params['stat_date']) && filled($params['stat_date'])) {
            $query->where('stat_date', $params['stat_date']);
        }

        if (isset($params['stat_date_range']) && filled($params['stat_date_range'])) {
            $query->whereBetween('stat_date', $params['stat_date_range']);
        }

        if (isset($params['limit_status']) && filled($params['limit_status'])) {
            $query->where('limit_status', $params['limit_status']);
        }

        if (isset($params['collection_success_rate_min']) && filled($params['collection_success_rate_min'])) {
            $query->where('collection_success_rate', '>=', $params['collection_success_rate_min']);
        }

        if (isset($params['collection_success_rate_max']) && filled($params['collection_success_rate_max'])) {
            $query->where('collection_success_rate', '<=', $params['collection_success_rate_max']);
        }

        if (isset($params['disbursement_success_rate_min']) && filled($params['disbursement_success_rate_min'])) {
            $query->where('disbursement_success_rate', '>=', $params['disbursement_success_rate_min']);
        }

        if (isset($params['disbursement_success_rate_max']) && filled($params['disbursement_success_rate_max'])) {
            $query->where('disbursement_success_rate', '<=', $params['disbursement_success_rate_max']);
        }

        if (isset($params['receipt_amount_min']) && filled($params['receipt_amount_min'])) {
            $query->where('receipt_amount', '>=', $params['receipt_amount_min']);
        }

        if (isset($params['receipt_amount_max']) && filled($params['receipt_amount_max'])) {
            $query->where('receipt_amount', '<=', $params['receipt_amount_max']);
        }

        if (isset($params['payment_amount_min']) && filled($params['payment_amount_min'])) {
            $query->where('payment_amount', '>=', $params['payment_amount_min']);
        }

        if (isset($params['payment_amount_max']) && filled($params['payment_amount_max'])) {
            $query->where('payment_amount', '<=', $params['payment_amount_max']);
        }

        return $query;
    }

    /**
     * 根据账户ID和日期查找统计记录
     */
    public function findByAccountAndDate(?int $channelAccountId, ?int $bankAccountId, string $statDate): ?ModelChannelAccountDailyStats
    {
        $query = $this->model->query()->where('stat_date', $statDate);

        // 根据账户类型设置查询条件
        if ($channelAccountId && $channelAccountId > 0) {
            // 上游渠道账户
            $query->where('channel_account_id', $channelAccountId)
                  ->where('bank_account_id', 0);
        } elseif ($bankAccountId && $bankAccountId > 0) {
            // 银行账户
            $query->where('channel_account_id', 0)
                  ->where('bank_account_id', $bankAccountId);
        } else {
            return null; // 没有有效的账户ID
        }

        return $query->first();
    }

    /**
     * 获取指定日期范围内的统计数据
     */
    public function getStatsByDateRange(string $startDate, string $endDate, array $filters = []): \Illuminate\Database\Eloquent\Collection
    {
        $query = $this->model->query()
            ->whereBetween('stat_date', [$startDate, $endDate])
            ->orderBy('stat_date', 'desc');

        if (isset($filters['channel_id']) && filled($filters['channel_id'])) {
            $query->where('channel_id', $filters['channel_id']);
        }

        if (isset($filters['limit_status']) && filled($filters['limit_status'])) {
            $query->where('limit_status', $filters['limit_status']);
        }

        return $query->get();
    }

    /**
     * 获取限额状态异常的账户统计
     */
    public function getLimitStatusAbnormal(string $statDate = null): \Illuminate\Database\Eloquent\Collection
    {
        $query = $this->model->query()
            ->where('limit_status', '>', 0)
            ->orderBy('limit_status', 'desc')
            ->orderBy('stat_date', 'desc');

        if ($statDate) {
            $query->where('stat_date', $statDate);
        }

        return $query->get();
    }

    /**
     * 更新或创建统计记录
     */
    public function updateOrCreateStats(array $conditions, array $data): ModelChannelAccountDailyStats
    {
        return $this->model->query()->updateOrCreate($conditions, $data);
    }

    public function page(array $params = [], ?int $page = null, ?int $pageSize = null): array
    {
        $result = $this->perQuery($this->getQuery(), $params)
            ->with('channel:id,channel_name,channel_code,channel_icon')
            ->with('channel_account:id,merchant_id')
            ->with('bank_account:id,branch_name')
            ->paginate(
            perPage: $pageSize,
            pageName: static::PER_PAGE_PARAM_NAME,
            page: $page,
        );
        return $this->handlePage($result);
    }

}
