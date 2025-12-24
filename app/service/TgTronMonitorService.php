<?php

namespace app\service;

use app\constants\TgTronTransactionLog as TxLogConst;
use app\repository\TgTronTransactionLogRepository;
use app\repository\TgGameGroupConfigRepository;
use app\repository\TgPlayerWalletBindingRepository;
use DI\Attribute\Inject;
use support\Db;
use support\Log;

/**
 * TRON区块链监控服务
 * @extends BaseService
 */
class TgTronMonitorService extends BaseService
{
    #[Inject]
    public TgTronTransactionLogRepository $repository;

    #[Inject]
    protected TgGameGroupConfigRepository $configRepository;

    #[Inject]
    protected TgPlayerWalletBindingRepository $bindingRepository;

    #[Inject]
    protected TgSnakeNodeService $nodeService;

    #[Inject]
    protected TgPrizeService $prizeService;

    #[Inject]
    protected TgGameGroupService $groupService;

    /**
     * 记录交易日志
     */
    public function logTransaction(array $data)
    {
        // 检查交易是否已存在
        if ($this->repository->exists($data['tx_hash'])) {
            Log::warning('交易已存在，跳过记录', ['tx_hash' => $data['tx_hash']]);
            return null;
        }

        return $this->repository->create([
            'group_id' => $data['group_id'],
            'tx_hash' => $data['tx_hash'],
            'from_address' => $data['from_address'],
            'to_address' => $data['to_address'],
            'amount' => $data['amount'],
            'transaction_type' => $data['transaction_type'],
            'block_height' => $data['block_height'] ?? 0,
            'block_timestamp' => $data['block_timestamp'] ?? time(),
            'status' => $data['status'] ?? TxLogConst::TX_STATUS_SUCCESS,
            'is_valid' => TxLogConst::VALID_YES,
            'invalid_reason' => null,
            'processed' => TxLogConst::PROCESSED_NO,
        ]);
    }

    /**
     * 验证交易有效性
     */
    public function validateTransaction(array $txData, $config): array
    {
        // 1. 检查交易金额是否达到最小投注金额
        if ($txData['amount'] < $config->min_bet_amount) {
            return [
                'valid' => false,
                'reason' => "交易金额不足最小投注金额: {$config->min_bet_amount} TRX",
            ];
        }

        // 2. 检查接收地址是否为当前钱包地址
        if (strtolower($txData['to_address']) !== strtolower($config->wallet_address)) {
            return [
                'valid' => false,
                'reason' => '接收地址不匹配当前钱包地址',
            ];
        }

        // 3. 检查交易状态
        if ($txData['status'] !== TxLogConst::TX_STATUS_SUCCESS) {
            return [
                'valid' => false,
                'reason' => '交易状态不是成功',
            ];
        }

        // 4. 检查钱包是否在变更中
        if ($config->wallet_change_status == ConfigConst::WALLET_CHANGE_STATUS_CHANGING) {
            return [
                'valid' => false,
                'reason' => '钱包变更中，暂停接受新交易',
            ];
        }

        return ['valid' => true];
    }

    /**
     * 处理入账交易（核心方法）
     */
    public function processIncomingTransaction(int $groupId, array $txData): array
    {
        try {
            Db::beginTransaction();

            // 获取群组配置
            $config = $this->configRepository->findById($groupId);
            if (!$config) {
                throw new \Exception('群组配置不存在');
            }

            // 记录交易日志
            $txLog = $this->logTransaction([
                'group_id' => $groupId,
                'tx_hash' => $txData['tx_hash'],
                'from_address' => $txData['from_address'],
                'to_address' => $txData['to_address'],
                'amount' => $txData['amount'],
                'transaction_type' => TxLogConst::TRANSACTION_TYPE_INCOMING,
                'block_height' => $txData['block_height'] ?? 0,
                'block_timestamp' => $txData['block_timestamp'] ?? time(),
                'status' => $txData['status'] ?? TxLogConst::TX_STATUS_SUCCESS,
            ]);

            if (!$txLog) {
                Db::rollBack();
                return [
                    'success' => false,
                    'message' => '交易已处理过',
                ];
            }

            // 验证交易有效性
            $validation = $this->validateTransaction($txData, $config);
            if (!$validation['valid']) {
                // 标记为无效交易
                $this->repository->markAsInvalid($txLog->id, $validation['reason']);
                Db::commit();

                return [
                    'success' => false,
                    'message' => '交易无效: ' . $validation['reason'],
                    'tx_log_id' => $txLog->id,
                ];
            }

            // 查询玩家信息（通过钱包地址绑定）
            $binding = $this->bindingRepository->getUserByWalletAddress($groupId, $txData['from_address']);

            // 创建节点
            $nodeResult = $this->nodeService->createNode([
                'group_id' => $groupId,
                'player_address' => $txData['from_address'],
                'player_tg_user_id' => $binding ? $binding->tg_user_id : null,
                'player_tg_username' => $binding ? $binding->tg_username : null,
                'amount' => $txData['amount'],
                'tx_hash' => $txData['tx_hash'],
            ]);

            if (!$nodeResult['success']) {
                throw new \Exception('创建节点失败: ' . $nodeResult['message']);
            }

            $node = $nodeResult['node'];

            // 标记交易为已处理
            $this->repository->markAsProcessed($txLog->id);

            // 更新群组奖池（增加金额）
            $group = $this->groupService->getByConfigId($groupId);
            if ($group) {
                $this->groupService->increasePrizePool($group->id, $txData['amount']);

                // 添加节点到蛇身
                $this->groupService->addSnakeNode($group->id, $node->id);
            }

            // 检查中奖
            $prizeResult = $this->prizeService->checkAndProcessPrize($groupId, $node->id);

            Db::commit();

            Log::info("处理入账交易成功", [
                'tx_hash' => $txData['tx_hash'],
                'node_id' => $node->id,
                'ticket' => $nodeResult['ticket'],
                'matched' => $prizeResult['matched'] ?? false,
            ]);

            return [
                'success' => true,
                'message' => '交易处理成功',
                'tx_log_id' => $txLog->id,
                'node' => $node,
                'ticket' => $nodeResult['ticket'],
                'ticket_serial_no' => $nodeResult['ticket_serial_no'],
                'prize_result' => $prizeResult,
            ];

        } catch (\Exception $e) {
            Db::rollBack();
            Log::error('处理入账交易失败: ' . $e->getMessage(), ['tx_data' => $txData]);
            return [
                'success' => false,
                'error' => true,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * 批量处理未处理的入账交易
     */
    public function processUnprocessedTransactions(int $groupId, int $limit = 100): array
    {
        $transactions = $this->repository->getUnprocessedIncomingTransactions($groupId, $limit);

        $results = [
            'total' => $transactions->count(),
            'success' => 0,
            'failed' => 0,
            'details' => [],
        ];

        foreach ($transactions as $tx) {
            $result = $this->processIncomingTransaction($groupId, [
                'tx_hash' => $tx->tx_hash,
                'from_address' => $tx->from_address,
                'to_address' => $tx->to_address,
                'amount' => $tx->amount,
                'block_height' => $tx->block_height,
                'block_timestamp' => $tx->block_timestamp,
                'status' => $tx->status,
            ]);

            if ($result['success']) {
                $results['success']++;
            } else {
                $results['failed']++;
            }

            $results['details'][] = $result;
        }

        return $results;
    }

    /**
     * 获取交易统计
     */
    public function getTransactionStatistics(int $groupId, string $dateStart = null, string $dateEnd = null): array
    {
        return $this->repository->getTransactionStatistics($groupId, $dateStart, $dateEnd);
    }

    /**
     * 获取最新的区块高度
     */
    public function getLatestBlockHeight(int $groupId): ?int
    {
        return $this->repository->getLatestBlockHeight($groupId);
    }

    /**
     * 记录出账交易（派奖转账）
     */
    public function logOutgoingTransaction(int $groupId, array $txData)
    {
        return $this->logTransaction([
            'group_id' => $groupId,
            'tx_hash' => $txData['tx_hash'],
            'from_address' => $txData['from_address'],
            'to_address' => $txData['to_address'],
            'amount' => $txData['amount'],
            'transaction_type' => TxLogConst::TRANSACTION_TYPE_OUTGOING,
            'block_height' => $txData['block_height'] ?? 0,
            'block_timestamp' => $txData['block_timestamp'] ?? time(),
            'status' => $txData['status'] ?? TxLogConst::TX_STATUS_SUCCESS,
        ]);
    }
}
