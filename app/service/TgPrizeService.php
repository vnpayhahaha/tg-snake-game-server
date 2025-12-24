<?php

namespace app\service;

use app\constants\TgPrizeRecord as PrizeConst;
use app\constants\TgPrizeTransfer as TransferConst;
use app\constants\TgSnakeNode as NodeConst;
use app\constants\TgPrizeDispatchQueue as QueueConst;
use app\repository\TgPrizeRecordRepository;
use app\repository\TgPrizeTransferRepository;
use app\repository\TgPrizeDispatchQueueRepository;
use app\repository\TgSnakeNodeRepository;
use app\repository\TgGameGroupRepository;
use app\repository\TgGameGroupConfigRepository;
use DI\Attribute\Inject;
use support\Db;
use support\Log;

/**
 * 中奖服务（核心业务逻辑）
 * @extends BaseService
 */
class TgPrizeService extends BaseService
{
    #[Inject]
    public TgPrizeRecordRepository $repository;

    #[Inject]
    protected TgPrizeTransferRepository $transferRepository;

    #[Inject]
    protected TgPrizeDispatchQueueRepository $queueRepository;

    #[Inject]
    protected TgSnakeNodeRepository $nodeRepository;

    #[Inject]
    protected TgGameGroupRepository $groupRepository;

    #[Inject]
    protected TgGameGroupConfigRepository $configRepository;

    #[Inject]
    protected TgGameGroupService $groupService;

    #[Inject]
    protected TgSnakeNodeService $nodeService;

    /**
     * 生成中奖流水号
     */
    public function generatePrizeSerialNo(int $groupId): string
    {
        $date = date('YmdHis');
        return sprintf('%s%s%s%s%s',
            PrizeConst::PRIZE_SERIAL_PREFIX,
            PrizeConst::SERIAL_SEPARATOR,
            $groupId,
            PrizeConst::SERIAL_SEPARATOR,
            $date
        );
    }

    /**
     * 检查并处理中奖（新节点加入后调用）
     */
    public function checkAndProcessPrize(int $groupId, int $newNodeId): array
    {
        try {
            Db::beginTransaction();

            $config = $this->configRepository->findById($groupId);
            if (!$config) {
                throw new \Exception('群组配置不存在');
            }

            $group = $this->groupRepository->getByConfigId($groupId);
            if (!$group) {
                throw new \Exception('游戏群组不存在');
            }

            // 获取新节点
            $newNode = $this->nodeRepository->findById($newNodeId);
            if (!$newNode) {
                throw new \Exception('节点不存在');
            }

            // 获取当前活跃节点（按创建时间排序）
            $activeNodes = $this->nodeRepository->getActiveNodes($groupId, $config->wallet_change_count);
            if ($activeNodes->isEmpty()) {
                Db::commit();
                return ['matched' => false, 'message' => '没有活跃节点'];
            }

            // 1. 先检查连号大奖（优先级最高）
            $jackpotResult = $this->checkJackpot($activeNodes, $config);
            if ($jackpotResult['matched']) {
                $prizeResult = $this->processJackpotPrize($group, $config, $jackpotResult);
                Db::commit();
                return $prizeResult;
            }

            // 2. 检查区间匹配
            $rangeResult = $this->checkRangeMatch($newNode, $activeNodes);
            if ($rangeResult['matched']) {
                $prizeResult = $this->processRangePrize($group, $config, $rangeResult);
                Db::commit();
                return $prizeResult;
            }

            Db::commit();
            return ['matched' => false, 'message' => '未中奖'];

        } catch (\Exception $e) {
            Db::rollBack();
            Log::error('检查中奖失败: ' . $e->getMessage());
            return [
                'matched' => false,
                'error' => true,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * 检查连号大奖（蛇身中连续相同数字）
     */
    protected function checkJackpot($activeNodes, $config): array
    {
        if ($activeNodes->count() < 3) {
            return ['matched' => false];
        }

        // 获取最后N个节点的凭证号
        $recentNodes = $activeNodes->sortByDesc('id')->take(6);
        $tickets = $recentNodes->pluck('ticket_number')->toArray();

        // 检查是否有连续相同的数字
        $consecutiveCount = 1;
        $lastTicket = null;
        $matchedNodes = [];

        foreach ($tickets as $ticket) {
            if ($lastTicket === null) {
                $lastTicket = $ticket;
                $matchedNodes[] = $ticket;
                continue;
            }

            if ($ticket === $lastTicket) {
                $consecutiveCount++;
                $matchedNodes[] = $ticket;

                // 达到连号条件（可配置，这里默认3个）
                if ($consecutiveCount >= 3) {
                    return [
                        'matched' => true,
                        'type' => PrizeConst::PRIZE_TYPE_JACKPOT,
                        'nodes' => $recentNodes->sortBy('id')->take($consecutiveCount),
                        'ticket' => $lastTicket,
                        'consecutive_count' => $consecutiveCount,
                    ];
                }
            } else {
                $consecutiveCount = 1;
                $lastTicket = $ticket;
                $matchedNodes = [$ticket];
            }
        }

        return ['matched' => false];
    }

    /**
     * 检查区间匹配（历史匹配）
     */
    protected function checkRangeMatch($newNode, $activeNodes): array
    {
        // 在历史节点中查找相同凭证号
        $matchedNode = null;
        foreach ($activeNodes as $node) {
            if ($node->id != $newNode->id && $node->ticket_number === $newNode->ticket_number) {
                $matchedNode = $node;
                break;
            }
        }

        if (!$matchedNode) {
            return ['matched' => false];
        }

        // 找到匹配，获取区间节点（包含首尾）
        $startNodeId = min($matchedNode->id, $newNode->id);
        $endNodeId = max($matchedNode->id, $newNode->id);

        $rangeNodes = $this->nodeRepository->getNodesBetween($startNodeId, $endNodeId);

        return [
            'matched' => true,
            'type' => PrizeConst::PRIZE_TYPE_RANGE,
            'start_node' => $matchedNode,
            'end_node' => $newNode,
            'nodes' => $rangeNodes,
            'ticket' => $newNode->ticket_number,
        ];
    }

    /**
     * 处理连号大奖
     */
    protected function processJackpotPrize($group, $config, array $jackpotData): array
    {
        $nodes = $jackpotData['nodes'];
        $nodeIds = $nodes->pluck('id')->toArray();

        // 计算中奖金额（清空奖池）
        $prizePool = $group->prize_pool_amount;
        $platformFee = bcmul($prizePool, $config->platform_fee_rate, 2);
        $prizeAmount = bcsub($prizePool, $platformFee, 2);
        $winnerCount = $nodes->count();
        $prizePerWinner = bcdiv($prizeAmount, $winnerCount, 2);

        // 创建中奖记录
        $prizeSerialNo = $this->generatePrizeSerialNo($group->id);
        $prizeRecord = $this->repository->create([
            'group_id' => $group->id,
            'prize_serial_no' => $prizeSerialNo,
            'wallet_cycle' => $config->wallet_change_count,
            'ticket_number' => $jackpotData['ticket'],
            'winner_node_id_first' => $nodes->first()->id,
            'winner_node_id_last' => $nodes->last()->id,
            'winner_node_ids' => implode(',', $nodeIds),
            'total_amount' => $nodes->sum('amount'),
            'platform_fee' => $platformFee,
            'fee_rate' => $config->platform_fee_rate,
            'prize_pool' => $prizePool,
            'prize_amount' => $prizeAmount,
            'prize_per_winner' => $prizePerWinner,
            'pool_remaining' => 0, // 连号大奖清空奖池
            'winner_count' => $winnerCount,
            'status' => PrizeConst::STATUS_PENDING,
            'version' => 1,
        ]);

        // 标记节点为已中奖
        $this->nodeRepository->markAsMatched($nodeIds, $prizeRecord->id);

        // 创建派奖任务
        $this->createDispatchTasks($prizeRecord, $nodes);

        // 更新群组奖池（清零）
        $this->groupService->updatePrizePool($group->id, 0);

        // 更新最后中奖信息
        $this->groupService->updateLastPrize($group->id, [
            'nodes' => implode(',', $nodeIds),
            'amount' => $prizeAmount,
            'address' => $nodes->first()->player_address,
            'serial_no' => $prizeSerialNo,
            'prize_at' => now(),
        ]);

        // 清空当前蛇身
        $this->groupService->clearCurrentSnake($group->id);

        Log::info("连号大奖中奖", [
            'prize_serial_no' => $prizeSerialNo,
            'ticket' => $jackpotData['ticket'],
            'consecutive_count' => $jackpotData['consecutive_count'],
            'prize_amount' => $prizeAmount,
            'winner_count' => $winnerCount,
        ]);

        return [
            'matched' => true,
            'type' => 'jackpot',
            'prize_record' => $prizeRecord,
            'prize_amount' => $prizeAmount,
            'winner_count' => $winnerCount,
            'prize_per_winner' => $prizePerWinner,
        ];
    }

    /**
     * 处理区间匹配中奖
     */
    protected function processRangePrize($group, $config, array $rangeData): array
    {
        $nodes = $rangeData['nodes'];
        $nodeIds = $nodes->pluck('id')->toArray();

        // 计算中奖金额（不清空奖池，从奖池中提取）
        $totalAmount = $nodes->sum('amount');
        $prizePool = $group->prize_pool_amount;

        // 中奖金额 = 区间总金额 + 奖池的一部分（比如50%）
        $poolContribution = bcmul($prizePool, '0.5', 2);
        $totalPrize = bcadd($totalAmount, $poolContribution, 2);

        $platformFee = bcmul($totalPrize, $config->platform_fee_rate, 2);
        $prizeAmount = bcsub($totalPrize, $platformFee, 2);
        $winnerCount = $nodes->count();
        $prizePerWinner = bcdiv($prizeAmount, $winnerCount, 2);

        $poolRemaining = bcsub($prizePool, $poolContribution, 2);
        if ($poolRemaining < 0) {
            $poolRemaining = 0;
        }

        // 创建中奖记录
        $prizeSerialNo = $this->generatePrizeSerialNo($group->id);
        $prizeRecord = $this->repository->create([
            'group_id' => $group->id,
            'prize_serial_no' => $prizeSerialNo,
            'wallet_cycle' => $config->wallet_change_count,
            'ticket_number' => $rangeData['ticket'],
            'winner_node_id_first' => $rangeData['start_node']->id,
            'winner_node_id_last' => $rangeData['end_node']->id,
            'winner_node_ids' => implode(',', $nodeIds),
            'total_amount' => $totalAmount,
            'platform_fee' => $platformFee,
            'fee_rate' => $config->platform_fee_rate,
            'prize_pool' => $prizePool,
            'prize_amount' => $prizeAmount,
            'prize_per_winner' => $prizePerWinner,
            'pool_remaining' => $poolRemaining,
            'winner_count' => $winnerCount,
            'status' => PrizeConst::STATUS_PENDING,
            'version' => 1,
        ]);

        // 标记节点为已中奖
        $this->nodeRepository->markAsMatched($nodeIds, $prizeRecord->id);

        // 创建派奖任务
        $this->createDispatchTasks($prizeRecord, $nodes);

        // 更新群组奖池
        $this->groupService->updatePrizePool($group->id, $poolRemaining);

        // 更新最后中奖信息
        $this->groupService->updateLastPrize($group->id, [
            'nodes' => implode(',', $nodeIds),
            'amount' => $prizeAmount,
            'address' => $rangeData['start_node']->player_address,
            'serial_no' => $prizeSerialNo,
            'prize_at' => now(),
        ]);

        // 从当前蛇身中移除已中奖节点
        $currentNodeIds = $this->groupService->getCurrentSnakeNodeIds($group->id);
        $remainingNodeIds = array_diff($currentNodeIds, $nodeIds);
        $this->groupService->updateSnakeNodes($group->id, $remainingNodeIds);

        Log::info("区间匹配中奖", [
            'prize_serial_no' => $prizeSerialNo,
            'ticket' => $rangeData['ticket'],
            'range' => [$rangeData['start_node']->id, $rangeData['end_node']->id],
            'prize_amount' => $prizeAmount,
            'winner_count' => $winnerCount,
        ]);

        return [
            'matched' => true,
            'type' => 'range',
            'prize_record' => $prizeRecord,
            'prize_amount' => $prizeAmount,
            'winner_count' => $winnerCount,
            'prize_per_winner' => $prizePerWinner,
        ];
    }

    /**
     * 创建派奖任务
     */
    protected function createDispatchTasks($prizeRecord, $nodes): void
    {
        foreach ($nodes as $node) {
            // 创建转账记录
            $transfer = $this->transferRepository->create([
                'prize_record_id' => $prizeRecord->id,
                'prize_serial_no' => $prizeRecord->prize_serial_no,
                'node_id' => $node->id,
                'to_address' => $node->player_address,
                'amount' => $prizeRecord->prize_per_winner,
                'tx_hash' => null,
                'status' => TransferConst::STATUS_PENDING,
                'retry_count' => 0,
                'error_message' => null,
            ]);

            // 创建派奖队列任务
            $this->queueRepository->createTask([
                'prize_record_id' => $prizeRecord->id,
                'prize_transfer_id' => $transfer->id,
                'group_id' => $prizeRecord->group_id,
                'prize_serial_no' => $prizeRecord->prize_serial_no,
                'priority' => QueueConst::PRIORITY_NORMAL,
                'status' => QueueConst::STATUS_PENDING,
                'retry_count' => 0,
                'max_retry' => QueueConst::DEFAULT_MAX_RETRY,
                'task_data' => json_encode([
                    'transfer_id' => $transfer->id,
                    'to_address' => $node->player_address,
                    'amount' => $prizeRecord->prize_per_winner,
                ]),
                'error_message' => null,
                'scheduled_at' => now(),
                'started_at' => null,
                'completed_at' => null,
                'version' => 1,
            ]);
        }
    }

    /**
     * 获取中奖记录统计
     */
    public function getGroupStatistics(int $groupId, string $dateStart = null, string $dateEnd = null): array
    {
        return $this->repository->getGroupStatistics($groupId, $dateStart, $dateEnd);
    }

    /**
     * 获取最近中奖记录
     */
    public function getRecentPrizes(int $groupId, int $limit = 10)
    {
        return $this->repository->getRecentPrizes($groupId, $limit);
    }
}
