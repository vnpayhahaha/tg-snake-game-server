<?php

namespace app\repository;

use app\constants\TgPrizeRecord as PrizeConst;
use app\model\ModelTgPrizeRecord;
use DI\Attribute\Inject;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Class TgPrizeRecordRepository.
 * @extends IRepository<ModelTgPrizeRecord>
 */
class TgPrizeRecordRepository extends IRepository
{
    #[Inject]
    protected ModelTgPrizeRecord $model;

    public function handleSearch(Builder $query, array $params): Builder
    {
        if (isset($params['group_id']) && filled($params['group_id'])) {
            $query->where('group_id', $params['group_id']);
        }

        if (isset($params['wallet_cycle']) && filled($params['wallet_cycle'])) {
            $query->where('wallet_cycle', $params['wallet_cycle']);
        }

        if (isset($params['status']) && filled($params['status'])) {
            $query->where('status', $params['status']);
        }

        if (isset($params['prize_serial_no']) && filled($params['prize_serial_no'])) {
            $query->where('prize_serial_no', $params['prize_serial_no']);
        }

        if (isset($params['date_start']) && filled($params['date_start'])) {
            $query->whereDate('created_at', '>=', $params['date_start']);
        }

        if (isset($params['date_end']) && filled($params['date_end'])) {
            $query->whereDate('created_at', '<=', $params['date_end']);
        }

        return $query;
    }

    /**
     * 根据群组ID查询中奖记录
     */
    public function getByGroupId(int $groupId, int $limit = 20): Collection
    {
        return $this->model::query()
            ->where('group_id', $groupId)
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * 根据钱包周期查询中奖记录
     */
    public function getByWalletCycle(int $groupId, int $walletCycle): Collection
    {
        return $this->model::query()
            ->where('group_id', $groupId)
            ->where('wallet_cycle', $walletCycle)
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * 根据流水号查询
     */
    public function getBySerialNo(string $serialNo): ?ModelTgPrizeRecord
    {
        return $this->model::query()
            ->where('prize_serial_no', $serialNo)
            ->first();
    }

    /**
     * 获取待处理的中奖记录
     */
    public function getPendingRecords(): Collection
    {
        return $this->model::query()
            ->where('status', PrizeConst::STATUS_PENDING)
            ->orderBy('created_at')
            ->get();
    }

    /**
     * 获取转账中的记录
     */
    public function getTransferringRecords(): Collection
    {
        return $this->model::query()
            ->where('status', PrizeConst::STATUS_TRANSFERRING)
            ->orderBy('created_at')
            ->get();
    }

    /**
     * 更新中奖记录状态
     */
    public function updateStatus(int $id, int $status): bool
    {
        return (bool)$this->model::query()
            ->whereKey($id)
            ->update(['status' => $status]);
    }

    /**
     * 使用乐观锁更新版本号
     */
    public function updateWithVersion(int $id, array $data, int $currentVersion): bool
    {
        $data['version'] = $currentVersion + 1;

        $affected = $this->model::query()
            ->where('id', $id)
            ->where('version', $currentVersion)
            ->update($data);

        return $affected > 0;
    }

    /**
     * 统计群组中奖数据
     */
    public function getGroupStatistics(int $groupId, string $dateStart = null, string $dateEnd = null): array
    {
        $query = $this->model::query()
            ->where('group_id', $groupId)
            ->where('status', PrizeConst::STATUS_COMPLETED); // 只统计已完成的

        if ($dateStart) {
            $query->whereDate('created_at', '>=', $dateStart);
        }

        if ($dateEnd) {
            $query->whereDate('created_at', '<=', $dateEnd);
        }

        $totalCount = $query->count();
        $totalPrizeAmount = $query->sum('prize_amount');
        $totalPlatformFee = $query->sum('platform_fee');
        $totalWinnerCount = $query->sum('winner_count');

        return [
            'total_count' => $totalCount,
            'total_prize_amount' => $totalPrizeAmount,
            'total_platform_fee' => $totalPlatformFee,
            'total_winner_count' => $totalWinnerCount,
        ];
    }

    /**
     * 统计当日中奖数据
     */
    public function getDailyStatistics(int $groupId, string $date = null): array
    {
        $date = $date ?? date('Y-m-d');

        return $this->model::query()
            ->where('group_id', $groupId)
            ->whereDate('created_at', $date)
            ->selectRaw('
                COUNT(*) as total_count,
                SUM(prize_amount) as total_prize_amount,
                SUM(platform_fee) as total_platform_fee,
                SUM(winner_count) as total_winner_count
            ')
            ->first()
            ->toArray();
    }

    /**
     * 获取最近N条中奖记录
     */
    public function getRecentPrizes(int $groupId, int $limit = 10): Collection
    {
        return $this->model::query()
            ->where('group_id', $groupId)
            ->where('status', PrizeConst::STATUS_COMPLETED)
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * 获取玩家相关的中奖记录（通过winner_node_ids查询）
     */
    public function getPlayerPrizeRecords(int $groupId, int $nodeId): Collection
    {
        return $this->model::query()
            ->where('group_id', $groupId)
            ->where(function ($query) use ($nodeId) {
                $query->where('winner_node_ids', 'like', $nodeId . ',%')
                    ->orWhere('winner_node_ids', 'like', '%,' . $nodeId . ',%')
                    ->orWhere('winner_node_ids', 'like', '%,' . $nodeId);
            })
            ->orderByDesc('created_at')
            ->get();
    }
}
