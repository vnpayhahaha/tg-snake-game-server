<?php

namespace app\process\task;

use app\constants\TgGameGroupConfig as ConfigConst;
use app\lib\helper\TelegramBotHelper;
use app\repository\TgGameGroupConfigRepository;
use app\service\TgGameGroupConfigService;
use Carbon\Carbon;
use DI\Attribute\Inject;
use support\Log;
use Workerman\Crontab\Crontab;

/**
 * 钱包变更检查进程
 * 定时检查冷却期已结束的钱包变更，并自动完成变更
 */
class WalletChangeCheckProcess
{
    #[Inject]
    protected TgGameGroupConfigRepository $configRepository;

    #[Inject]
    protected TgGameGroupConfigService $configService;

    public function onWorkerStart(): void
    {
        Log::info("WalletChangeCheckProcess: 进程启动");

        // 每分钟检查一次钱包变更状态
        new Crontab('0 * * * * *', function() {
            try {
                $this->checkWalletChanges();
            } catch (\Throwable $e) {
                Log::error("WalletChangeCheckProcess执行失败: " . $e->getMessage(), [
                    'trace' => $e->getTraceAsString()
                ]);
            }
        });

        Log::info("WalletChangeCheckProcess: Crontab已设置 (每分钟执行一次)");
    }

    /**
     * 检查钱包变更
     */
    protected function checkWalletChanges(): void
    {
        // 确保数据库连接可用
        $this->ensureDatabaseConnection();

        // 获取所有正在变更中的配置
        $changingConfigs = $this->configRepository->getChangingConfigs();

        if ($changingConfigs->isEmpty()) {
            Log::debug("WalletChangeCheckProcess: 没有正在变更的钱包");
            return;
        }

        Log::info("WalletChangeCheckProcess: 检查 {$changingConfigs->count()} 个正在变更的钱包");

        foreach ($changingConfigs as $config) {
            try {
                $this->checkSingleWalletChange($config);
            } catch (\Throwable $e) {
                Log::error("检查群组 {$config->id} 钱包变更失败: " . $e->getMessage(), [
                    'group_id' => $config->id,
                    'wallet_address' => $config->wallet_address,
                    'pending_wallet_address' => $config->pending_wallet_address,
                ]);
            }
        }

        Log::info("WalletChangeCheckProcess: 本次检查完成");
    }

    /**
     * 检查单个钱包变更
     */
    protected function checkSingleWalletChange($config): void
    {
        // 检查是否到达结束时间
        $now = Carbon::now();
        $endAt = Carbon::parse($config->wallet_change_end_at);

        if ($now->lt($endAt)) {
            Log::debug("钱包变更冷却期未结束", [
                'group_id' => $config->id,
                'end_at' => $endAt->toDateTimeString(),
                'remaining_seconds' => $now->diffInSeconds($endAt, false),
            ]);
            return;
        }

        // 冷却期已结束，完成变更
        Log::info("钱包变更冷却期已结束，开始执行变更", [
            'group_id' => $config->id,
            'old_wallet_address' => $config->wallet_address,
            'new_wallet_address' => $config->pending_wallet_address,
            'wallet_change_count' => $config->wallet_change_count,
        ]);

        $result = $this->configService->completeWalletChange($config->id);

        if ($result['success']) {
            Log::info("钱包变更完成", [
                'group_id' => $config->id,
                'new_address' => $result['new_address'],
                'new_wallet_cycle' => $result['new_wallet_cycle'],
                'archived_nodes' => $result['archived_nodes'],
            ]);

            // 发送Telegram通知给群组管理员
            $this->sendTelegramNotification($config, $result);
        } else {
            Log::error("钱包变更完成失败", [
                'group_id' => $config->id,
                'error' => $result['message'],
            ]);
        }
    }

    /**
     * 发送Telegram通知
     * @param $config
     * @param array $result
     */
    protected function sendTelegramNotification($config, array $result): void
    {
        try {
            $message = "🔄 钱包变更完成通知\n\n" .
                      "群组：{$config->tg_group_name}\n" .
                      "新钱包地址：{$result['new_address']}\n" .
                      "钱包周期：#{$result['new_wallet_cycle']}\n" .
                      "归档节点数：{$result['archived_nodes']}\n\n" .
                      "✅ 钱包变更已完成，系统已恢复正常运行";

            TelegramBotHelper::send($config->tg_chat_id, $message);

            Log::info("发送Telegram通知成功", [
                'group_id' => $config->id,
                'tg_chat_id' => $config->tg_chat_id,
                'new_address' => $result['new_address'],
            ]);
        } catch (\Throwable $e) {
            Log::error("发送Telegram通知失败: " . $e->getMessage(), [
                'group_id' => $config->id,
                'tg_chat_id' => $config->tg_chat_id,
                'trace' => $e->getTraceAsString()
            ]);
        }
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
}
