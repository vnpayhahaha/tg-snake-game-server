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
}
