<?php

namespace app\process\task;

use app\lib\helper\TronWebHelper;
use app\service\TgGameGroupConfigService;
use app\service\TgTronMonitorService;
use DI\Attribute\Inject;
use support\Log;
use Workerman\Crontab\Crontab;

/**
 * TRON区块链交易监控进程
 * 定时监控所有活跃群组的TRON钱包地址的入账交易
 */
class TronTransactionMonitorProcess
{
    #[Inject]
    protected TgGameGroupConfigService $configService;

    #[Inject]
    protected TgTronMonitorService $monitorService;

    #[Inject]
    protected TronWebHelper $tronHelper;

    public function onWorkerStart(): void
    {
        Log::info("TronTransactionMonitorProcess: 进程启动");

        // 每10秒检查一次TRON交易
        new Crontab('*/10 * * * * *', function() {
            try {
                $this->monitorTransactions();
            } catch (\Throwable $e) {
                Log::error("TronTransactionMonitorProcess执行失败: " . $e->getMessage(), [
                    'trace' => $e->getTraceAsString()
                ]);
            }
        });

        Log::info("TronTransactionMonitorProcess: Crontab已设置 (每10秒执行一次)");
    }

    /**
     * 监控交易
     */
    protected function monitorTransactions(): void
    {
        // 确保数据库连接可用（处理断线重连）
        $this->ensureDatabaseConnection();

        // 获取所有活跃配置
        $activeConfigs = $this->configService->getActiveConfigs();

        if ($activeConfigs->isEmpty()) {
            Log::debug("TronTransactionMonitorProcess: 没有活跃的群组配置");
            return;
        }

        Log::info("TronTransactionMonitorProcess: 开始监控 {$activeConfigs->count()} 个群组");

        $groupIndex = 0;
        $totalGroups = $activeConfigs->count();

        foreach ($activeConfigs as $config) {
            try {
                $this->monitorGroupTransactions($config);
            } catch (\Throwable $e) {
                Log::error("监控群组 {$config->id} 交易失败: " . $e->getMessage(), [
                    'group_id' => $config->id,
                    'wallet_address' => $config->wallet_address,
                ]);
            }

            // 在群组之间添加延迟，避免触发TronGrid API速率限制（每秒3次）
            // 最后一个群组不需要延迟
            $groupIndex++;
            if ($groupIndex < $totalGroups) {
                usleep(500000); // 500毫秒延迟
            }
        }

        Log::info("TronTransactionMonitorProcess: 本次监控完成");
    }

    /**
     * 确保数据库连接可用
     * 处理 "MySQL server has gone away" 问题
     */
    protected function ensureDatabaseConnection(): void
    {
        try {
            // 尝试执行简单查询来检查连接
            \support\Db::connection()->select('SELECT 1');
        } catch (\Throwable $e) {
            // 如果连接失败，重新连接
            Log::warning("数据库连接断开，正在重新连接...", [
                'error' => $e->getMessage()
            ]);

            try {
                // 断开当前连接
                \support\Db::connection()->disconnect();
                // 重新连接
                \support\Db::connection()->reconnect();

                Log::info("数据库重新连接成功");
            } catch (\Throwable $reconnectError) {
                Log::error("数据库重新连接失败: " . $reconnectError->getMessage());
                throw $reconnectError;
            }
        }
    }

    /**
     * 监控单个群组的交易
     */
    protected function monitorGroupTransactions($config): void
    {
        // 获取上次检查的区块高度
        $lastBlockHeight = $this->monitorService->getLatestBlockHeight($config->id) ?? 0;

        try {
            // 使用TronWebHelper获取钱包地址的交易历史
            $transactions = $this->tronHelper->getTransactionHistory(
                $config->wallet_address,
                $lastBlockHeight
            );

            // 如果没有新交易，直接返回
            if (empty($transactions)) {
                Log::debug("群组 {$config->id} 没有新交易", [
                    'wallet_address' => $config->wallet_address,
                    'last_block_height' => $lastBlockHeight,
                ]);
                return;
            }

            Log::info("发现 " . count($transactions) . " 笔新交易", [
                'group_id' => $config->id,
                'wallet_address' => $config->wallet_address,
            ]);

            // 按区块高度正序排序（从旧到新），确保写入顺序正确
            usort($transactions, function ($a, $b) {
                return $a['block_height'] <=> $b['block_height'];
            });

            // 处理每笔交易（所有交易都记录，通过验证逻辑判断是否有效）
            foreach ($transactions as $tx) {
                // 调用监控服务处理入账交易
                $result = $this->monitorService->processIncomingTransaction($config->id, [
                    'tx_hash' => $tx['tx_hash'],
                    'from_address' => $tx['from_address'],
                    'to_address' => $tx['to_address'],
                    'amount' => $tx['amount'],  // SUN单位
                    'block_height' => $tx['block_height'],
                    'block_timestamp' => $tx['block_timestamp'],
                    'status' => $tx['status'],  // SUCCESS/FAILED
                    'contract_type' => $tx['contract_type'] ?? 'Unknown', // 交易类型
                ]);

                if ($result['success']) {
                    Log::info("处理入账交易成功", [
                        'group_id' => $config->id,
                        'tx_hash' => $tx['tx_hash'],
                        'amount_sun' => $tx['amount'],
                        'amount_trx' => TronWebHelper::sunToTrx($tx['amount']),
                        'block_height' => $tx['block_height'],
                        'contract_type' => $tx['contract_type'] ?? 'Unknown',
                    ]);
                } else {
                    Log::info("记录交易（无效或已处理）", [
                        'group_id' => $config->id,
                        'tx_hash' => $tx['tx_hash'],
                        'message' => $result['message'],
                        'contract_type' => $tx['contract_type'] ?? 'Unknown',
                    ]);
                }
            }

            // 记录监控完成
            $latestBlock = max(array_column($transactions, 'block_height'));

            Log::info("群组交易监控完成", [
                'group_id' => $config->id,
                'processed_count' => count($transactions),
                'last_block_height' => $lastBlockHeight,
                'latest_block' => $latestBlock,
            ]);

        } catch (\Throwable $e) {
            Log::error("监控群组交易异常: " . $e->getMessage(), [
                'group_id' => $config->id,
                'wallet_address' => $config->wallet_address,
                'last_block_height' => $lastBlockHeight,
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * 处理未处理的交易（补偿机制）
     */
    protected function processUnprocessedTransactions(): void
    {
        $activeConfigs = $this->configService->getActiveConfigs();

        foreach ($activeConfigs as $config) {
            try {
                $result = $this->monitorService->processUnprocessedTransactions($config->id, 100);
                if ($result['total'] > 0) {
                    Log::info("处理未处理交易", [
                        'group_id' => $config->id,
                        'total' => $result['total'],
                        'success' => $result['success'],
                        'failed' => $result['failed'],
                    ]);
                }
            } catch (\Throwable $e) {
                Log::error("处理群组 {$config->id} 未处理交易失败: " . $e->getMessage());
            }
        }
    }
}
