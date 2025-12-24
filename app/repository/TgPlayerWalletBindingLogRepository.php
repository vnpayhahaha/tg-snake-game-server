<?php

namespace app\repository;

use app\model\ModelTgPlayerWalletBindingLog;
use DI\Attribute\Inject;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Class TgPlayerWalletBindingLogRepository.
 * @extends IRepository<ModelTgPlayerWalletBindingLog>
 */
class TgPlayerWalletBindingLogRepository extends IRepository
{
    #[Inject]
    protected ModelTgPlayerWalletBindingLog $model;

    public function handleSearch(Builder $query, array $params): Builder
    {
        if (isset($params['group_id']) && filled($params['group_id'])) {
            $query->where('group_id', $params['group_id']);
        }

        if (isset($params['tg_user_id']) && filled($params['tg_user_id'])) {
            $query->where('tg_user_id', $params['tg_user_id']);
        }

        if (isset($params['tg_username']) && filled($params['tg_username'])) {
            $query->where('tg_username', 'like', '%' . $params['tg_username'] . '%');
        }

        if (isset($params['change_type']) && filled($params['change_type'])) {
            $query->where('change_type', $params['change_type']);
        }

        if (isset($params['old_wallet_address']) && filled($params['old_wallet_address'])) {
            $query->where('old_wallet_address', $params['old_wallet_address']);
        }

        if (isset($params['new_wallet_address']) && filled($params['new_wallet_address'])) {
            $query->where('new_wallet_address', $params['new_wallet_address']);
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
     * 记录绑定变更日志
     */
    public function logChange(array $data): ModelTgPlayerWalletBindingLog
    {
        return $this->model::query()->create($data);
    }

    /**
     * 获取用户的绑定历史
     */
    public function getUserBindingHistory(int $groupId, int $tgUserId, int $limit = 20): Collection
    {
        return $this->model::query()
            ->where('group_id', $groupId)
            ->where('tg_user_id', $tgUserId)
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * 获取钱包地址的绑定历史
     */
    public function getWalletBindingHistory(string $walletAddress, int $limit = 20): Collection
    {
        return $this->model::query()
            ->where(function ($query) use ($walletAddress) {
                $query->where('old_wallet_address', $walletAddress)
                    ->orWhere('new_wallet_address', $walletAddress);
            })
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * 获取群组的绑定变更记录
     */
    public function getGroupBindingLogs(int $groupId, int $limit = 50): Collection
    {
        return $this->model::query()
            ->where('group_id', $groupId)
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * 根据变更类型查询
     */
    public function getByChangeType(int $groupId, int $changeType, int $limit = 50): Collection
    {
        return $this->model::query()
            ->where('group_id', $groupId)
            ->where('change_type', $changeType)
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * 统计用户绑定变更次数
     */
    public function countUserChanges(int $groupId, int $tgUserId): int
    {
        return $this->model::query()
            ->where('group_id', $groupId)
            ->where('tg_user_id', $tgUserId)
            ->count();
    }

    /**
     * 统计群组绑定变更数据
     */
    public function getGroupStatistics(int $groupId, string $dateStart = null, string $dateEnd = null): array
    {
        $query = $this->model::query()
            ->where('group_id', $groupId);

        if ($dateStart) {
            $query->whereDate('created_at', '>=', $dateStart);
        }

        if ($dateEnd) {
            $query->whereDate('created_at', '<=', $dateEnd);
        }

        $totalCount = $query->count();
        $firstBindCount = (clone $query)->where('change_type', 1)->count();
        $updateBindCount = (clone $query)->where('change_type', 2)->count();

        return [
            'total_count' => $totalCount,
            'first_bind_count' => $firstBindCount,
            'update_bind_count' => $updateBindCount,
        ];
    }

    /**
     * 获取最近的绑定变更记录
     */
    public function getRecentLogs(int $groupId, int $limit = 10): Collection
    {
        return $this->model::query()
            ->where('group_id', $groupId)
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * 清理旧日志（超过指定天数）
     */
    public function cleanOldLogs(int $daysAgo = 180): int
    {
        return $this->model::query()
            ->where('created_at', '<', now()->subDays($daysAgo))
            ->delete();
    }
}
