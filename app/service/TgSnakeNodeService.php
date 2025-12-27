<?php

namespace app\service;

use app\constants\TgSnakeNode as NodeConst;
use app\repository\TgSnakeNodeRepository;
use app\repository\TgGameGroupConfigRepository;
use DI\Attribute\Inject;
use support\Db;
use support\Log;

/**
 * 蛇身节点服务
 * @extends BaseService
 */
class TgSnakeNodeService extends BaseService
{
    #[Inject]
    public TgSnakeNodeRepository $repository;

    #[Inject]
    protected TgGameGroupConfigRepository $configRepository;

    /**
     * 根据交易哈希提取凭证号（取最后6位十六进制字符）
     */
    public function extractTicketFromTxHash(string $txHash): string
    {
        // 移除 0x 前缀
        $hash = str_starts_with($txHash, '0x') ? substr($txHash, 2) : $txHash;
        // 取最后6位
        $ticket = substr($hash, -6);
        return strtoupper($ticket);
    }

    /**
     * 生成凭证流水号
     */
    public function generateTicketSerialNo(int $groupId, string $ticket): string
    {
        $date = date('YmdHis');
        return sprintf('%s%s%s%s%s',
            NodeConst::TICKET_PREFIX,
            NodeConst::SERIAL_SEPARATOR,
            $groupId,
            NodeConst::SERIAL_SEPARATOR,
            $date . $ticket
        );
    }

    /**
     * 创建节点
     */
    public function createNode(array $data): array
    {
        try {
            Db::beginTransaction();

            // 检查交易哈希是否已存在
            $existing = $this->repository->findByTxHash($data['tx_hash']);
            if ($existing) {
                throw new \Exception('该交易已处理，请勿重复提交');
            }

            // 获取群组配置
            $config = $this->configRepository->findById($data['group_id']);
            if (!$config) {
                throw new \Exception('群组配置不存在');
            }

            // 验证金额是否达到固定投注金额
            if ($data['amount'] != $config->bet_amount) {
                throw new \Exception("投注金额必须为固定金额: {$config->bet_amount} TRX");
            }

            // 提取凭证号
            $ticket = $this->extractTicketFromTxHash($data['tx_hash']);

            // 生成凭证流水号
            $ticketSerialNo = $this->generateTicketSerialNo($data['group_id'], $ticket);

            // 创建节点
            $node = $this->repository->create([
                'group_id' => $data['group_id'],
                'wallet_cycle' => $config->wallet_change_count,
                'player_address' => $data['player_address'],
                'player_tg_user_id' => $data['player_tg_user_id'] ?? null,
                'player_tg_username' => $data['player_tg_username'] ?? null,
                'amount' => $data['amount'],
                'tx_hash' => $data['tx_hash'],
                'ticket_number' => $ticket,
                'ticket_serial_no' => $ticketSerialNo,
                'status' => NodeConst::STATUS_ACTIVE,
                'matched_prize_id' => null,
            ]);

            Db::commit();

            Log::info("创建节点成功", [
                'node_id' => $node->id,
                'group_id' => $data['group_id'],
                'ticket' => $ticket,
                'amount' => $data['amount'],
            ]);

            return [
                'success' => true,
                'message' => '购彩成功',
                'node' => $node,
                'ticket' => $ticket,
                'ticket_serial_no' => $ticketSerialNo,
            ];
        } catch (\Exception $e) {
            Db::rollBack();
            Log::error('创建节点失败: ' . $e->getMessage(), ['data' => $data]);
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * 获取活跃节点
     */
    public function getActiveNodes(int $groupId, int $walletCycle = null)
    {
        return $this->repository->getActiveNodes($groupId, $walletCycle);
    }

    /**
     * 获取活跃节点ID列表
     */
    public function getActiveNodeIds(int $groupId): array
    {
        return $this->repository->getActiveNodeIds($groupId);
    }

    /**
     * 归档节点（钱包变更时）
     */
    public function archiveNodes(int $groupId, int $walletCycle): int
    {
        return $this->repository->archiveNodes($groupId, $walletCycle);
    }

    /**
     * 标记节点为已中奖
     */
    public function markAsMatched(array $nodeIds, int $prizeRecordId): int
    {
        return $this->repository->markAsMatched($nodeIds, $prizeRecordId);
    }

    /**
     * 获取玩家的购彩记录
     */
    public function getPlayerTickets(int $groupId, string $playerAddress, int $limit = 10)
    {
        return $this->repository->getPlayerTickets($groupId, $playerAddress, $limit);
    }

    /**
     * 获取玩家统计数据
     */
    public function getPlayerStats(int $groupId, string $playerAddress): array
    {
        return $this->repository->getPlayerStats($groupId, $playerAddress);
    }

    /**
     * 获取玩家在当前蛇身中的活跃节点（通过Telegram用户ID）
     */
    public function getPlayerActiveNodes(int $groupId, int $tgUserId)
    {
        return Db::table('tg_snake_node')
            ->where('group_id', $groupId)
            ->where('player_tg_user_id', $tgUserId)
            ->where('status', NodeConst::STATUS_ACTIVE)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * 根据凭证流水号查询
     */
    public function findByTicketSerialNo(string $serialNo)
    {
        return $this->repository->findByTicketSerialNo($serialNo);
    }

    /**
     * 根据交易哈希查询
     */
    public function findByTxHash(string $txHash)
    {
        return $this->repository->findByTxHash($txHash);
    }

    /**
     * 获取区间内的节点
     */
    public function getNodesBetween(int $startNodeId, int $endNodeId)
    {
        return $this->repository->getNodesBetween($startNodeId, $endNodeId);
    }

    /**
     * 统计当日节点数
     */
    public function countDailyNodes(int $groupId, string $date = null): int
    {
        return $this->repository->countDailyNodes($groupId, $date);
    }

    /**
     * 根据凭证流水号查询（别名方法）
     */
    public function getBySerialNo(string $serialNo)
    {
        return $this->findByTicketSerialNo($serialNo);
    }

    /**
     * 根据交易哈希查询（别名方法）
     */
    public function getByTxHash(string $txHash)
    {
        return $this->findByTxHash($txHash);
    }

    /**
     * 获取群组的活跃节点（控制器使用）
     */
    public function getActiveNodesByGroup(int $groupId, int $limit = 100)
    {
        return $this->repository->getActiveNodes($groupId)->take($limit);
    }

    /**
     * 根据钱包轮换周期获取节点
     */
    public function getNodesByWalletCycle(int $groupId, int $walletCycle)
    {
        $params = [
            'group_id' => $groupId,
            'wallet_cycle' => $walletCycle,
        ];
        return $this->repository->list($params);
    }

    /**
     * 获取群组的归档节点
     */
    public function getArchivedNodesByGroup(int $groupId, int $limit = 100)
    {
        $params = [
            'group_id' => $groupId,
            'status' => NodeConst::STATUS_ARCHIVED,
        ];
        return $this->repository->list($params)->take($limit);
    }

    /**
     * 根据玩家获取节点
     */
    public function getNodesByPlayer(string $playerAddress, int $groupId = null, int $limit = 50)
    {
        $query = Db::table('tg_snake_node')
            ->where('player_address', $playerAddress);

        if ($groupId) {
            $query->where('group_id', $groupId);
        }

        return $query->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * 获取每日统计
     */
    public function getDailyStatistics(int $groupId = null, string $date = null): array
    {
        if (!$date) {
            $date = date('Y-m-d');
        }

        $dateStart = $date . ' 00:00:00';
        $dateEnd = $date . ' 23:59:59';

        $query = Db::table('tg_snake_node')
            ->whereBetween('created_at', [$dateStart, $dateEnd]);

        if ($groupId) {
            $query->where('group_id', $groupId);
        }

        $stats = [
            'total_nodes' => (clone $query)->count(),
            'active_nodes' => (clone $query)->where('status', NodeConst::STATUS_ACTIVE)->count(),
            'archived_nodes' => (clone $query)->where('status', NodeConst::STATUS_ARCHIVED)->count(),
            'matched_nodes' => (clone $query)->whereNotNull('matched_prize_id')->count(),
            'total_amount' => (clone $query)->sum('amount'),
        ];

        return $stats;
    }

    /**
     * 获取群组统计
     */
    public function getGroupStatistics(int $groupId, string $dateStart = null, string $dateEnd = null): array
    {
        $query = Db::table('tg_snake_node')
            ->where('group_id', $groupId);

        if ($dateStart) {
            $query->where('created_at', '>=', $dateStart);
        }

        if ($dateEnd) {
            $query->where('created_at', '<=', $dateEnd);
        }

        $stats = [
            'total_nodes' => (clone $query)->count(),
            'active_nodes' => (clone $query)->where('status', NodeConst::STATUS_ACTIVE)->count(),
            'archived_nodes' => (clone $query)->where('status', NodeConst::STATUS_ARCHIVED)->count(),
            'matched_nodes' => (clone $query)->whereNotNull('matched_prize_id')->count(),
            'total_amount' => (clone $query)->sum('amount'),
            'unique_players' => (clone $query)->distinct('player_address')->count('player_address'),
        ];

        return $stats;
    }

    /**
     * 获取导出数据
     */
    public function getExportData(array $params, int $limit = 10000)
    {
        return $this->repository->list($params)->take($limit);
    }

    /**
     * 归档单个节点
     */
    public function archiveNode(int $id): array
    {
        try {
            $node = $this->repository->findById($id);
            if (!$node) {
                return [
                    'success' => false,
                    'message' => '节点不存在',
                ];
            }

            if ($node->status == NodeConst::STATUS_ARCHIVED) {
                return [
                    'success' => false,
                    'message' => '节点已归档',
                ];
            }

            $this->repository->updateById($id, ['status' => NodeConst::STATUS_ARCHIVED]);

            return [
                'success' => true,
                'message' => '节点已归档',
            ];
        } catch (\Exception $e) {
            Log::error('归档节点失败: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * 批量归档节点
     */
    public function batchArchiveNodes(array $nodeIds): array
    {
        try {
            Db::beginTransaction();

            $updated = Db::table('tg_snake_node')
                ->whereIn('id', $nodeIds)
                ->update(['status' => NodeConst::STATUS_ARCHIVED]);

            Db::commit();

            return [
                'success' => true,
                'message' => "已归档 {$updated} 个节点",
                'count' => $updated,
            ];
        } catch (\Exception $e) {
            Db::rollBack();
            Log::error('批量归档节点失败: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * 更新节点状态
     */
    public function updateStatus(int $id, int $status): bool
    {
        return $this->repository->updateById($id, ['status' => $status]);
    }
}
