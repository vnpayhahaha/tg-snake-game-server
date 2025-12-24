<?php

namespace app\process\task;

use app\service\TgGameGroupConfigService;
use app\service\TgTronMonitorService;
use DI\Attribute\Inject;
use support\Container;
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

    public function onWorkerStart(): void
    {
        Log::info("TronTransactionMonitorProcess: 进程启动");

        // 每30秒检查一次TRON交易
        new Crontab('*/30 * * * * *', function() {
            try {
                $this->monitorTransactions();
            } catch (\Throwable $e) {
                Log::error("TronTransactionMonitorProcess执行失败: " . $e->getMessage(), [
                    'trace' => $e->getTraceAsString()
                ]);
            }
        });

        Log::info("TronTransactionMonitorProcess: Crontab已设置 (每30秒执行一次)");
    }

    /**
     * 监控交易
     */
    protected function monitorTransactions(): void
    {
        $configService = Container::get(TgGameGroupConfigService::class);
        $monitorService = Container::get(TgTronMonitorService::class);

        // 获取所有活跃配置
        $activeConfigs = $configService->getActiveConfigs();

        if ($activeConfigs->isEmpty()) {
            Log::debug("TronTransactionMonitorProcess: 没有活跃的群组配置");
            return;
        }

        Log::info("TronTransactionMonitorProcess: 开始监控 {$activeConfigs->count()} 个群组");

        foreach ($activeConfigs as $config) {
            try {
                $this->monitorGroupTransactions($config, $monitorService);
            } catch (\Throwable $e) {
                Log::error("监控群组 {$config->id} 交易失败: " . $e->getMessage(), [
                    'group_id' => $config->id,
                    'wallet_address' => $config->wallet_address,
                ]);
            }
        }

        Log::info("TronTransactionMonitorProcess: 本次监控完成");
    }

    /**
     * 监控单个群组的交易
     */
    protected function monitorGroupTransactions($config, $monitorService): void
    {
        // TODO: 这里需要调用TRON API获取最新交易
        // 示例伪代码：
        // 1. 获取上次检查的区块高度
        $lastBlockHeight = $monitorService->getLatestBlockHeight($config->id) ?? 0;

        // 2. 调用TRON API查询该地址的新交易（需要实现TronWebHelper）
        // $tronHelper = Container::get(TronWebHelper::class);
        // $transactions = $tronHelper->getTransactionsByAddress(
        //     $config->wallet_address,
        //     $lastBlockHeight
        // );

        // 3. 处理每笔入账交易
        // foreach ($transactions as $tx) {
        //     if ($tx['type'] === 'incoming') {
        //         $result = $monitorService->processIncomingTransaction($config->id, [
        //             'tx_hash' => $tx['tx_hash'],
        //             'from_address' => $tx['from'],
        //             'to_address' => $tx['to'],
        //             'amount' => $tx['amount'],
        //             'block_height' => $tx['block_height'],
        //             'block_timestamp' => $tx['timestamp'],
        //             'status' => $tx['status'],
        //         ]);
        //
        //         if ($result['success']) {
        //             Log::info("处理入账交易成功", [
        //                 'group_id' => $config->id,
        //                 'tx_hash' => $tx['tx_hash'],
        //                 'amount' => $tx['amount'],
        //             ]);
        //         }
        //     }
        // }

        Log::debug("监控群组交易", [
            'group_id' => $config->id,
            'wallet_address' => $config->wallet_address,
            'last_block_height' => $lastBlockHeight,
        ]);
    }

    /**
     * 处理未处理的交易（补偿机制）
     */
    protected function processUnprocessedTransactions(): void
    {
        $configService = Container::get(TgGameGroupConfigService::class);
        $monitorService = Container::get(TgTronMonitorService::class);

        $activeConfigs = $configService->getActiveConfigs();

        foreach ($activeConfigs as $config) {
            try {
                $result = $monitorService->processUnprocessedTransactions($config->id, 100);
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
