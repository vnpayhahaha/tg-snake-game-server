<?php

namespace http\tenant\Service;

use app\constants\DisbursementOrder;
use app\lib\annotation\Cacheable;
use app\service\DisbursementOrderService as BasicDisbursementOrderService;
use Workerman\Coroutine\Parallel;

class DisbursementOrderService extends BasicDisbursementOrderService
{
    // 分析统计最近一周的订单
    #[Cacheable(
        prefix: 'disbursement:collection:order:number:tenantId',
        value: '_#{tenantId}}',
        ttl: 60,
        group: 'redis'
    )]
    protected function tenantStatisticsOrderNumberOfWeek(string $tenantId): array
    {
        // 计算近7天的日期范围，每天的订单数量
        $parallel = new Parallel(7);
        for ($i = 6; $i >= 0; $i--) {
            $parallel->add(function () use ($i, $tenantId) {
                $date = date('Y-m-d', strtotime('-' . $i . ' day'));
                $date_range[$date] = $this->repository->getQuery()
                    ->where('tenant_id', $tenantId)
                    ->where('created_at', '>=', $date)
                    ->where('created_at', '<', date('Y-m-d', strtotime('+1 day', strtotime($date))))
                    ->count();
                return $date_range;
            });
        }
        $results = $parallel->wait();
        // order_num_range 合并 $results 的值
        $order_num_range = array_merge(...$results);
        // $order_num_range 数组排序
        ksort($order_num_range);
        $today = date('Y-m-d');
        $yesterday = date('Y-m-d', strtotime('-1 day'));
        $endDate = date('Y-m-d', strtotime('+1 day'));
        $startDate = date('Y-m-d', strtotime('-6 days'));
        return [
            'count'     => $order_num_range[$today],
            'yesterday' => $order_num_range[$yesterday],
            'growth'    => (int)bcsub($order_num_range[$today], $order_num_range[$yesterday], 0),
            'chartData' => format_chart_data_x_y_date_count($order_num_range, $startDate, $endDate),
        ];
    }


    #[Cacheable(
        prefix: 'statistics:disbursement:order:successful:tenantId',
        value: '_#{tenantId}}',
        ttl: 60,
        group: 'redis'
    )]
    protected function tenantStatisticsOrderSuccessfulNumberOfWeek(string $tenantId): array
    {
        // 计算近7天的日期范围，每天的成功订单数量
        $parallel = new Parallel(7);
        for ($i = 6; $i >= 0; $i--) {
            $parallel->add(function () use ($i, $tenantId) {
                $date = date('Y-m-d', strtotime('-' . $i . ' day'));
                $date_range[$date] = $this->repository->getQuery()
                    ->where('tenant_id', $tenantId)
                    ->where('status', DisbursementOrder::STATUS_SUCCESS)
                    ->where('pay_time', '>=', $date)
                    ->where('pay_time', '<', date('Y-m-d', strtotime('+1 day', strtotime($date))))
                    ->count();
                return $date_range;
            });
        }
        $results = $parallel->wait();
        // order_num_range 合并 $results 的值
        $order_num_range = array_merge(...$results);
        // $order_num_range 数组排序
        ksort($order_num_range);
        $today = date('Y-m-d');
        $yesterday = date('Y-m-d', strtotime('-1 day'));
        $endDate = date('Y-m-d', strtotime('+1 day'));
        $startDate = date('Y-m-d', strtotime('-6 days'));
        return [
            'count'     => $order_num_range[$today],
            'yesterday' => $order_num_range[$yesterday],
            'growth'    => (int)bcsub($order_num_range[$today], $order_num_range[$yesterday], 0),
            'chartData' => format_chart_data_x_y_date_count($order_num_range, $startDate, $endDate),
        ];
    }

    #[Cacheable(
        prefix: 'statistics:disbursement:order:amount:tenantId',
        value: '_#{tenantId}}',
        ttl: 60,
        group: 'redis'
    )]
    protected function tenantStatisticsOrderSuccessfulAmountOfWeek(string $tenantId): array
    {
        // 计算近7天的日期范围，每天的成功订单数量
        $parallel = new Parallel(7);
        for ($i = 6; $i >= 0; $i--) {
            $parallel->add(function () use ($i, $tenantId) {
                $date = date('Y-m-d', strtotime('-' . $i . ' day'));
                $total = $this->repository->getQuery()
                    ->where('tenant_id', $tenantId)
                    ->where('status', DisbursementOrder::STATUS_SUCCESS)
                    ->where('pay_time', '>=', $date)
                    ->where('pay_time', '<', date('Y-m-d', strtotime('+1 day', strtotime($date))))
                    ->sum('amount');
                $date_range[$date] = number_format($total, 2, '.', ',');
                return $date_range;
            });
        }
        $results = $parallel->wait();
        // order_num_range 合并 $results 的值
        $order_num_range = array_merge(...$results);
        // $order_num_range 数组排序
        ksort($order_num_range);
        $today = date('Y-m-d');
        $yesterday = date('Y-m-d', strtotime('-1 day'));
        $endDate = date('Y-m-d', strtotime('+1 day'));
        $startDate = date('Y-m-d', strtotime('-6 days'));
        return [
            'count'     => $order_num_range[$today],
            'yesterday' => $order_num_range[$yesterday],
            'growth'    => bcsub($order_num_range[$today], $order_num_range[$yesterday], 0),
            'chartData' => format_chart_data_x_y_date_count($order_num_range, $startDate, $endDate, '₹'),
        ];
    }

    #[Cacheable(
        prefix: 'statistics:disbursement-success-order:hour-today:tenantId',
        value: '_#{tenantId}}',
        ttl: 60,
        group: 'redis'
    )]
    protected function tenantGetSuccessOrderCountByHourToday(string $tenantId): array
    {
        return $this->tenantGetSuccessOrderCountByHour($tenantId, date('Y-m-d'), date('Y-m-d'));
    }


    #[Cacheable(
        prefix: 'statistics:disbursement-success-order:hour-yesterday:tenantId',
        value: '_#{tenantId}}',
        ttl: 60,
        group: 'redis'
    )]
    protected function tenantGetSuccessOrderCountByHourYesterday(string $tenantId): array
    {
        return $this->tenantGetSuccessOrderCountByHour($tenantId, date('Y-m-d', strtotime('-1 day')), date('Y-m-d', strtotime('-1 day')));
    }

    #[Cacheable(
        prefix: 'statistics:disbursement-success-order:hour-week:tenantId',
        value: '_#{tenantId}}',
        ttl: 60,
        group: 'redis'
    )]
    protected function tenantGetSuccessOrderCountByHourWeek(string $tenantId): array
    {
        return $this->tenantGetSuccessOrderCountByHour($tenantId, date('Y-m-d', strtotime('-7 day')), date('Y-m-d'));
    }

}