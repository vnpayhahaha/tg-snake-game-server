<?php

namespace app\process\task;

use app\service\ChannelAccountDailyStatsService;
use DI\Attribute\Inject;
use Workerman\Crontab\Crontab;

class ChannelAccountDailyStatsCrontab
{
    #[Inject]
    protected ChannelAccountDailyStatsService $service;

    public function onWorkerStart(): void
    {
        // 每分钟实时统计任务
        new Crontab('0 */5 * * * *', function () {
            echo date('Y-m-d H:i:s') . "\n" . '每5分钟实时统计任务开始执行';
            $this->service->minutelyStatsCron();
        });

        // 每日定时1点统计（保留用于历史数据统计）
        new Crontab('0 0 */1 * * *', function () {
            echo date('Y-m-d H:i:s') . "\n" . '每日定时1点执行一次 统计订单任务开始执行';
            $this->service->dailyStatsCron();
        });
    }
}