<?php

namespace app\process\task;

use app\service\CollectionOrderService;
use DI\Attribute\Inject;
use Workerman\Crontab\Crontab;

class CollectionOrderCancelCrontab
{
    #[Inject]
    protected CollectionOrderService $service;

    public function onWorkerStart(): void
    {
        // 每分钟执行一次
        new Crontab('0 */1 * * * *', function () {
            echo date('Y-m-d H:i:s') . "\n" . '每分钟执行一次 订单取消任务开始执行';
            $this->service->orderExpire();
        });

    }
}