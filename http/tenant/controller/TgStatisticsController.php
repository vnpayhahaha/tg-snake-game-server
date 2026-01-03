<?php

namespace http\tenant\controller;

use app\controller\BasicController;
use app\router\Annotations\GetMapping;
use app\router\Annotations\RestController;
use http\tenant\Service\TgGameStatisticsService;
use DI\Attribute\Inject;
use support\Request;
use support\Response;

/**
 * 租户端TG游戏统计控制器
 */
#[RestController("/tenant/tg_statistics")]
class TgStatisticsController extends BasicController
{
    #[Inject]
    protected TgGameStatisticsService $statisticsService;

    /**
     * 获取租户概览数据
     */
    #[GetMapping('/overview')]
    public function overview(Request $request): Response
    {
        $tenantId = $request->user->tenant_id;

        $data = $this->statisticsService->getTenantOverview($tenantId);

        return $this->success(data: $data);
    }

    /**
     * 获取群组排行榜
     */
    #[GetMapping('/group_ranking')]
    public function groupRanking(Request $request): Response
    {
        $tenantId = $request->user->tenant_id;
        $sortBy = $request->input('sort_by', 'total_bet_amount');

        // 验证排序字段
        $allowedSortFields = ['total_bet_amount', 'total_prize_amount', 'total_nodes', 'total_platform_fee'];
        if (!in_array($sortBy, $allowedSortFields, true)) {
            $sortBy = 'total_bet_amount';
        }

        $data = $this->statisticsService->getGroupRanking($tenantId, $sortBy);

        return $this->success(data: $data);
    }

    /**
     * 获取每日统计数据
     */
    #[GetMapping('/daily')]
    public function daily(Request $request): Response
    {
        $tenantId = $request->user->tenant_id;
        $dateStart = $request->input('date_start');
        $dateEnd = $request->input('date_end');

        $data = $this->statisticsService->getDailyStatistics($tenantId, $dateStart, $dateEnd);

        return $this->success(data: $data);
    }

    /**
     * 获取趋势图数据
     */
    #[GetMapping('/trend')]
    public function trend(Request $request): Response
    {
        $tenantId = $request->user->tenant_id;
        $days = (int)$request->input('days', 7);

        // 限制天数范围
        $days = max(1, min($days, 90));

        $data = $this->statisticsService->getTrendData($tenantId, $days);

        return $this->success(data: $data);
    }
}
