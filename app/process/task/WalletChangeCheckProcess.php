<?php

namespace app\process\task;

use app\constants\TgGameGroupConfig as ConfigConst;
use app\repository\TgGameGroupConfigRepository;
use app\service\TgGameGroupConfigService;
use Carbon\Carbon;
use DI\Attribute\Inject;
use support\Container;
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
        $configRepository = Container::get(TgGameGroupConfigRepository::class);
        $configService = Container::get(TgGameGroupConfigService::class);

        // 获取所有正在变更中的配置
        $changingConfigs = $configRepository->getChangingConfigs();

        if ($changingConfigs->isEmpty()) {
            Log::debug("WalletChangeCheckProcess: 没有正在变更的钱包");
            return;
        }

        Log::info("WalletChangeCheckProcess: 检查 {$changingConfigs->count()} 个正在变更的钱包");

        foreach ($changingConfigs as $config) {
            try {
                $this->checkSingleWalletChange($config, $configService);
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
    protected function checkSingleWalletChange($config, $configService): void
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

        $result = $configService->completeWalletChange($config->id);

        if ($result['success']) {
            Log::info("钱包变更完成", [
                'group_id' => $config->id,
                'new_address' => $result['new_address'],
                'new_wallet_cycle' => $result['new_wallet_cycle'],
                'archived_nodes' => $result['archived_nodes'],
            ]);

            // TODO: 发送Telegram通知给群组管理员
            // $this->sendTelegramNotification($config, $result);
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
        // TODO: 实现Telegram通知功能
        // 需要使用TelegramBotHelper发送消息到群组

        Log::info("发送Telegram通知: 钱包变更完成", [
            'group_id' => $config->id,
            'tg_chat_id' => $config->tg_chat_id,
            'new_address' => $result['new_address'],
        ]);
    }
}
