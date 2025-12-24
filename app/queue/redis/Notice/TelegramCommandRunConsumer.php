<?php

namespace app\queue\redis\Notice;

use app\service\bot\CommandEnum;
use app\service\bot\TelegramService;
use DI\Attribute\Inject;
use support\Log;
use Throwable;
use Webman\RedisQueue\Consumer;

class TelegramCommandRunConsumer implements Consumer
{
    // 要消费的队列名
    public string $queue = CommandEnum::TELEGRAM_COMMAND_RUN_QUEUE_NAME;

    // 连接名，对应 plugin/webman/redis-queue/redis.php 里的连接`
    public string $connection = 'default';
    #[Inject]
    protected TelegramService $telegramService;

    public function consume($data)
    {
        var_dump('TELEGRAM_COMMAND_RUN_QUEUE_NAME==', $data);
        return $this->telegramService->commandRunConsumer($data);

    }

    /**
     * 消费失败处理
     *
     * @param Throwable $e
     * @param $package
     * @return void
     */
    public function onConsumeFailure(Throwable $e, $package)
    {
        dump('TELEGRAM_COMMAND_RUN_QUEUE_NAME===========onConsumeFailure=====', $e, $package);
        $data = $package['data'] ?? [];
        Log::error('TelegramCommandRunConsumer consume failure', [
            'exception' => $e->getMessage(),
            'data'      => $data
        ]);
    }
}