<?php

namespace app\service;

use app\repository\UserLoginLogRepository;
use DI\Attribute\Inject;

/**
 * @extends IService<UserLoginLogRepository>
 */
final class UserLoginLogService extends IService
{
    #[Inject]
    protected UserLoginLogRepository $repository;

    public function statisticsLoginCountOfLast10Days(string $username): array
    {
        $endDate = date('Y-m-d');
        $startDate = date('Y-m-d', strtotime('-9 days'));

        $result = $this->repository->getQuery()
            ->selectRaw('DATE(login_time) as date, COUNT(*) as login_count')
            ->where('username', $username)
            ->where('status', 1)
            ->whereBetween('login_time', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date')
            ->toArray();

        $xAxis = [];
        $chartData = [];
        for ($i = 9; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-{$i} days"));
            $count = $result[$date]['login_count'] ?? 0;
            $xAxis[] = $date;
            $chartData[] = (int)$count;
        }

        return [
            'xAxis'     => $xAxis,
            'chartData' => $chartData
        ];
    }

}
