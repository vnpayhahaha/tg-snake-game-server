<?php

namespace app\queue\redis\Notice;

use app\service\bot\CommandEnum;
use app\service\bot\TelegramService;
use DI\Attribute\Inject;
use support\Log;
use Throwable;
use Webman\RedisQueue\Consumer;

class TelegramNoticeConsumer implements Consumer
{
    // 要消费的队列名
    public string $queue = CommandEnum::TELEGRAM_NOTICE_QUEUE_NAME;

    // 连接名，对应 plugin/webman/redis-queue/redis.php 里的连接`
    public string $connection = 'default';

    #[Inject]
    protected TelegramService $telegramService;
    public function consume($data)
    {
        var_dump('TelegramNoticeConsumer==', $data);
        return  $this->telegramService->sendMessageConsumer($data);
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
        dump('TELEGRAM_NOTICE_QUEUE_NAME===========onConsumeFailure=====', $e, $package);
        $data = $package['data'] ?? [];
        Log::error('TelegramNoticeConsumer consume failure', [
            'exception' => $e->getMessage(),
            'data'      => $data
        ]);
    }
}