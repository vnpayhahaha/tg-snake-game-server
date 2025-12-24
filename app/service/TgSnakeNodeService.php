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

            // 验证金额是否达到最小投注金额
            if ($data['amount'] < $config->min_bet_amount) {
                throw new \Exception("投注金额不足最小金额: {$config->min_bet_amount} TRX");
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
}
