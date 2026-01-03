<?php

namespace http\tenant\Service;

use app\repository\TgGameGroupConfigRepository;
use app\repository\TgSnakeNodeRepository;
use app\repository\TgPrizeRecordRepository;
use DI\Attribute\Inject;

/**
 * 租户端TG游戏统计服务
 */
class TgGameStatisticsService
{
    #[Inject]
    protected TgGameGroupConfigRepository $configRepository;

    #[Inject]
    protected TgSnakeNodeRepository $nodeRepository;

    #[Inject]
    protected TgPrizeRecordRepository $prizeRepository;

    /**
     * 获取租户概览数据
     */
    public function getTenantOverview(string $tenantId): array
    {
        // 获取租户下所有群组ID
        $groupIds = $this->configRepository->getGroupIdsByTenantId($tenantId);

        // 群组统计
        $groupStats = $this->configRepository->getTenantGroupStats($tenantId);

        // 节点统计
        $nodeStats = $this->nodeRepository->getTenantStatistics($groupIds);

        // 中奖统计
        $prizeStats = $this->prizeRepository->getTenantStatistics($groupIds);

        // 今日统计
        $todayNodeStats = $this->nodeRepository->getTenantTodayStatistics($groupIds);
        $todayPrizeStats = $this->prizeRepository->getTenantTodayStatistics($groupIds);

        return [
            'total_groups' => $groupStats['total_groups'],
            'active_groups' => $groupStats['active_groups'],
            'total_bet_amount' => $nodeStats['total_amount'],
            'total_prize_amount' => $prizeStats['total_prize_amount'],
            'total_platform_fee' => $prizeStats['total_platform_fee'],
            'total_players' => $nodeStats['unique_players'],
            'total_nodes' => $nodeStats['total_nodes'],
            'total_prize_count' => $prizeStats['total_count'],
            'today' => [
                'bet_amount' => $todayNodeStats['amount'],
                'prize_amount' => $todayPrizeStats['prize_amount'],
                'nodes' => $todayNodeStats['nodes'],
                'prize_count' => $todayPrizeStats['count'],
            ],
        ];
    }

    /**
     * 获取群组排行榜
     */
    public function getGroupRanking(string $tenantId, string $sortBy = 'total_bet_amount'): array
    {
        // 获取租户下所有群组ID
        $groupIds = $this->configRepository->getGroupIdsByTenantId($tenantId);

        if (empty($groupIds)) {
            return ['list' => []];
        }

        // 获取群组配置信息
        $configs = $this->configRepository->getByTenantId($tenantId);
        $configMap = [];
        foreach ($configs as $config) {
            if ($config->group) {
                $configMap[$config->group->id] = [
                    'config_id' => $config->id,
                    'tg_chat_id' => $config->tg_chat_id,
                    'tg_chat_title' => $config->tg_chat_title,
                    'status' => $config->status,
                ];
            }
        }

        // 获取节点统计
        $nodeStats = $this->nodeRepository->getGroupNodeStats($groupIds);
        $nodeStatsMap = [];
        foreach ($nodeStats as $stat) {
            $nodeStatsMap[$stat->group_id] = [
                'total_nodes' => $stat->total_nodes,
                'total_bet_amount' => $stat->total_amount,
                'active_nodes' => $stat->active_nodes,
            ];
        }

        // 获取中奖统计
        $prizeStats = $this->prizeRepository->getGroupPrizeStats($groupIds);
        $prizeStatsMap = [];
        foreach ($prizeStats as $stat) {
            $prizeStatsMap[$stat->group_id] = [
                'total_prize_count' => $stat->total_count,
                'total_prize_amount' => $stat->total_prize_amount,
                'total_platform_fee' => $stat->total_platform_fee,
            ];
        }

        // 合并数据
        $list = [];
        foreach ($groupIds as $groupId) {
            $config = $configMap[$groupId] ?? [];
            $nodeStat = $nodeStatsMap[$groupId] ?? ['total_nodes' => 0, 'total_bet_amount' => 0, 'active_nodes' => 0];
            $prizeStat = $prizeStatsMap[$groupId] ?? ['total_prize_count' => 0, 'total_prize_amount' => 0, 'total_platform_fee' => 0];

            $list[] = array_merge([
                'group_id' => $groupId,
            ], $config, $nodeStat, $prizeStat);
        }

        // 排序
        usort($list, function ($a, $b) use ($sortBy) {
            return ($b[$sortBy] ?? 0) <=> ($a[$sortBy] ?? 0);
        });

        return ['list' => $list];
    }

    /**
     * 获取每日统计数据
     */
    public function getDailyStatistics(string $tenantId, string $dateStart = null, string $dateEnd = null): array
    {
        $groupIds = $this->configRepository->getGroupIdsByTenantId($tenantId);

        if (empty($groupIds)) {
            return ['list' => []];
        }

        // 默认最近7天
        $days = 7;
        if ($dateStart && $dateEnd) {
            $days = (int)ceil((strtotime($dateEnd) - strtotime($dateStart)) / 86400) + 1;
            $days = min($days, 90); // 最多90天
        }

        $nodeTrend = $this->nodeRepository->getTenantDailyTrend($groupIds, $days);
        $prizeTrend = $this->prizeRepository->getTenantDailyTrend($groupIds, $days);

        // 合并数据
        $prizeMap = [];
        foreach ($prizeTrend as $item) {
            $prizeMap[$item['date']] = $item;
        }

        $list = [];
        foreach ($nodeTrend as $item) {
            $prizeData = $prizeMap[$item['date']] ?? ['count' => 0, 'prize_amount' => 0, 'platform_fee' => 0];
            $list[] = [
                'date' => $item['date'],
                'node_count' => $item['count'],
                'bet_amount' => $item['amount'],
                'prize_count' => $prizeData['count'],
                'prize_amount' => $prizeData['prize_amount'],
                'platform_fee' => $prizeData['platform_fee'],
            ];
        }

        return ['list' => $list];
    }

    /**
     * 获取趋势图数据
     */
    public function getTrendData(string $tenantId, int $days = 7): array
    {
        $groupIds = $this->configRepository->getGroupIdsByTenantId($tenantId);

        if (empty($groupIds)) {
            return [
                'dates' => [],
                'bet_amount' => [],
                'prize_amount' => [],
                'node_count' => [],
            ];
        }

        $nodeTrend = $this->nodeRepository->getTenantDailyTrend($groupIds, $days);
        $prizeTrend = $this->prizeRepository->getTenantDailyTrend($groupIds, $days);

        // 构建日期范围
        $dates = [];
        $betAmounts = [];
        $prizeAmounts = [];
        $nodeCounts = [];

        // 填充日期
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-{$i} days"));
            $dates[] = $date;
            $betAmounts[$date] = 0;
            $prizeAmounts[$date] = 0;
            $nodeCounts[$date] = 0;
        }

        // 填充节点数据
        foreach ($nodeTrend as $item) {
            if (isset($betAmounts[$item['date']])) {
                $betAmounts[$item['date']] = $item['amount'];
                $nodeCounts[$item['date']] = $item['count'];
            }
        }

        // 填充中奖数据
        foreach ($prizeTrend as $item) {
            if (isset($prizeAmounts[$item['date']])) {
                $prizeAmounts[$item['date']] = $item['prize_amount'];
            }
        }

        return [
            'dates' => $dates,
            'bet_amount' => array_values($betAmounts),
            'prize_amount' => array_values($prizeAmounts),
            'node_count' => array_values($nodeCounts),
        ];
    }
}
