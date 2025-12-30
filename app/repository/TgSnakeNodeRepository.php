<?php

namespace app\repository;

use app\constants\TgSnakeNode as NodeConst;
use app\model\ModelTgSnakeNode;
use DI\Attribute\Inject;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Class TgSnakeNodeRepository.
 * @extends IRepository<ModelTgSnakeNode>
 */
class TgSnakeNodeRepository extends IRepository
{
    #[Inject]
    protected ModelTgSnakeNode $model;

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

        if (isset($params['player_address']) && filled($params['player_address'])) {
            $query->where('player_address', $params['player_address']);
        }
        if (isset($params['wallet_address']) && filled($params['wallet_address'])) {
            $query->where('wallet_address', $params['wallet_address']);
        }

        if (isset($params['ticket_serial_no']) && filled($params['ticket_serial_no'])) {
            $query->where('ticket_serial_no', $params['ticket_serial_no']);
        }

        if (isset($params['tx_hash']) && filled($params['tx_hash'])) {
            $query->where('tx_hash', $params['tx_hash']);
        }

        return $query;
    }

    /**
     * 获取活跃节点（按创建时间排序）
     */
    public function getActiveNodes(int $groupId, int $walletCycle = null): Collection
    {
        $query = $this->model::query()
            ->where('group_id', $groupId)
            ->where('status', NodeConst::STATUS_ACTIVE);

        if ($walletCycle !== null) {
            $query->where('wallet_cycle', $walletCycle);
        }

        return $query->orderBy('created_at')
            ->orderBy('id')
            ->get();
    }

    /**
     * 获取活跃节点ID列表
     */
    public function getActiveNodeIds(int $groupId): array
    {
        return $this->model::query()
            ->where('group_id', $groupId)
            ->where('status', NodeConst::STATUS_ACTIVE)
            ->orderBy('created_at')
            ->orderBy('id')
            ->pluck('id')
            ->toArray();
    }

    /**
     * 根据钱包周期查询节点
     */
    public function getNodesByWalletCycle(int $groupId, int $walletCycle, int $status = null): Collection
    {
        $query = $this->model::query()
            ->where('group_id', $groupId)
            ->where('wallet_cycle', $walletCycle);

        if ($status !== null) {
            $query->where('status', $status);
        }

        return $query->orderBy('created_at')->get();
    }

    /**
     * 归档节点（钱包变更时）
     */
    public function archiveNodes(int $groupId, int $walletCycle): int
    {
        return $this->model::query()
            ->where('group_id', $groupId)
            ->where('wallet_cycle', $walletCycle)
            ->where('status', NodeConst::STATUS_ACTIVE)
            ->update(['status' => NodeConst::STATUS_ARCHIVED]);
    }

    /**
     * 批量归档指定节点ID
     */
    public function archiveNodesByIds(array $nodeIds): int
    {
        return $this->model::query()
            ->whereIn('id', $nodeIds)
            ->update(['status' => NodeConst::STATUS_ARCHIVED]);
    }

    /**
     * 统计当日节点数
     */
    public function countDailyNodes(int $groupId, string $date = null): int
    {
        $date = $date ?? date('Y-m-d');

        return $this->model::query()
            ->where('group_id', $groupId)
            ->whereDate('created_at', $date)
            ->count();
    }

    /**
     * 获取区间内的节点（包含起始和结束节点）
     */
    public function getNodesBetween(int $startNodeId, int $endNodeId): Collection
    {
        return $this->model::query()
            ->whereBetween('id', [$startNodeId, $endNodeId])
            ->orderBy('id')
            ->get();
    }

    /**
     * 根据交易哈希查询（防重复）
     */
    public function findByTxHash(string $txHash): ?ModelTgSnakeNode
    {
        return $this->model::query()
            ->where('tx_hash', $txHash)
            ->first();
    }

    /**
     * 根据凭证流水号查询
     */
    public function findByTicketSerialNo(string $serialNo): ?ModelTgSnakeNode
    {
        return $this->model::query()
            ->where('ticket_serial_no', $serialNo)
            ->first();
    }

    /**
     * 标记节点为已中奖
     */
    public function markAsMatched(array $nodeIds, int $prizeRecordId): int
    {
        return $this->model::query()
            ->whereIn('id', $nodeIds)
            ->update([
                'status' => NodeConst::STATUS_MATCHED,
                'matched_prize_id' => $prizeRecordId,
            ]);
    }

    /**
     * 获取玩家的购彩记录
     */
    public function getPlayerTickets(int $groupId, string $playerAddress, int $limit = 10): Collection
    {
        return $this->model::query()
            ->where('group_id', $groupId)
            ->where('player_address', $playerAddress)
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * 统计玩家购彩数据
     */
    public function getPlayerStats(int $groupId, string $playerAddress): array
    {
        $totalCount = $this->model::query()
            ->where('group_id', $groupId)
            ->where('player_address', $playerAddress)
            ->count();

        $wonCount = $this->model::query()
            ->where('group_id', $groupId)
            ->where('player_address', $playerAddress)
            ->where('status', NodeConst::STATUS_MATCHED)
            ->count();

        $totalAmount = $this->model::query()
            ->where('group_id', $groupId)
            ->where('player_address', $playerAddress)
            ->sum('amount');

        return [
            'total_count' => $totalCount,
            'won_count' => $wonCount,
            'total_amount' => $totalAmount,
        ];
    }

    /**
     * 获取玩家在当前蛇身中的活跃节点（通过Telegram用户ID）
     */
    public function getPlayerActiveNodesByTgUserId(int $groupId, int $tgUserId): Collection
    {
        return $this->model::query()
            ->where('group_id', $groupId)
            ->where('player_tg_user_id', $tgUserId)
            ->where('status', NodeConst::STATUS_ACTIVE)
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * 获取玩家已中奖的节点对应的中奖记录ID（通过Telegram用户ID）
     */
    public function getPlayerMatchedPrizeIds(int $groupId, int $tgUserId): array
    {
        return $this->model::query()
            ->where('group_id', $groupId)
            ->where('player_tg_user_id', $tgUserId)
            ->whereNotNull('matched_prize_id')
            ->pluck('matched_prize_id')
            ->unique()
            ->toArray();
    }

    /**
     * 根据玩家地址获取节点
     */
    public function getNodesByPlayerAddress(string $playerAddress, int $groupId = null, int $limit = 50): Collection
    {
        $query = $this->model::query()
            ->where('player_address', $playerAddress);

        if ($groupId) {
            $query->where('group_id', $groupId);
        }

        return $query->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * 获取每日统计
     */
    public function getDailyStatistics(int $groupId = null, string $date = null): array
    {
        $date = $date ?? date('Y-m-d');
        $dateStart = $date . ' 00:00:00';
        $dateEnd = $date . ' 23:59:59';

        $query = $this->model::query()
            ->whereBetween('created_at', [$dateStart, $dateEnd]);

        if ($groupId) {
            $query->where('group_id', $groupId);
        }

        return [
            'total_nodes' => (clone $query)->count(),
            'active_nodes' => (clone $query)->where('status', NodeConst::STATUS_ACTIVE)->count(),
            'archived_nodes' => (clone $query)->where('status', NodeConst::STATUS_ARCHIVED)->count(),
            'matched_nodes' => (clone $query)->whereNotNull('matched_prize_id')->count(),
            'total_amount' => (clone $query)->sum('amount'),
        ];
    }

    /**
     * 获取群组统计
     */
    public function getGroupStatistics(int $groupId, string $dateStart = null, string $dateEnd = null): array
    {
        $query = $this->model::query()
            ->where('group_id', $groupId);

        if ($dateStart) {
            $query->where('created_at', '>=', $dateStart);
        }

        if ($dateEnd) {
            $query->where('created_at', '<=', $dateEnd);
        }

        return [
            'total_nodes' => (clone $query)->count(),
            'active_nodes' => (clone $query)->where('status', NodeConst::STATUS_ACTIVE)->count(),
            'archived_nodes' => (clone $query)->where('status', NodeConst::STATUS_ARCHIVED)->count(),
            'matched_nodes' => (clone $query)->whereNotNull('matched_prize_id')->count(),
            'total_amount' => (clone $query)->sum('amount'),
            'unique_players' => (clone $query)->distinct('player_address')->count('player_address'),
        ];
    }

    /**
     * 根据钱包地址获取玩家节点统计
     */
    public function getPlayerStatsByWalletAddress(int $groupId, string $walletAddress): array
    {
        $query = $this->model::query()
            ->where('group_id', $groupId)
            ->where('player_address', $walletAddress);

        return [
            'total_nodes' => (clone $query)->count(),
            'active_nodes' => (clone $query)->where('status', NodeConst::STATUS_ACTIVE)->count(),
            'archived_nodes' => (clone $query)->where('status', NodeConst::STATUS_ARCHIVED)->count(),
            'total_amount' => (clone $query)->sum('amount'),
        ];
    }

    /**
     * 根据钱包地址获取玩家节点
     */
    public function getNodesByWalletAddress(int $groupId, string $walletAddress): Collection
    {
        return $this->model::query()
            ->where('group_id', $groupId)
            ->where('player_address', $walletAddress)
            ->orderByDesc('created_at')
            ->get();
    }
}
