<?php

namespace http\backend\controller;

use app\controller\BasicController;
use app\lib\annotation\OperationLog;
use app\lib\annotation\Permission;
use app\router\Annotations\GetMapping;
use app\router\Annotations\RestController;
use app\service\ChannelAccountDailyStatsService;
use DI\Attribute\Inject;
use support\Request;
use support\Response;

#[RestController("/admin/channel")]
class ChannelAccountDailyStatsController extends BasicController
{
    #[Inject]
    protected ChannelAccountDailyStatsService $service;

    #[GetMapping('/channel_account_daily_stats/list')]
    #[Permission(code: 'channel:channel_account:daily_stats')]
    #[OperationLog('渠道账户每日统计列表')]
    public function pageList(Request $request): Response
    {
        return $this->success(
            data: $this->service->page(
                $request->all(),
                $this->getCurrentPage(),
                $this->getPageSize(),
            )
        );
    }
}
