<?php

namespace http\backend\controller;

use app\controller\BasicController;
use app\lib\enum\ResultCode;
use app\router\Annotations\GetMapping;
use app\router\Annotations\RestController;
use app\service\CollectionOrderService;
use app\service\DisbursementOrderService;
use app\service\TenantUserLoginLogService;
use app\service\UserLoginLogService;
use DI\Attribute\Inject;
use support\Request;
use support\Response;

#[RestController("/admin/analysis")]
class AnalysisController extends BasicController
{
    #[Inject]
    public CollectionOrderService $collectionOrderService;
    #[Inject]
    public DisbursementOrderService $disbursementOrderService;

    #[Inject]
    protected UserLoginLogService $userLoginLogService;

    // 一周订单统计
    #[GetMapping('/weekOrder/collection_order_num')]
    public function weekOrderCollectionOrderNum(Request $request): Response
    {
        $user = $request->user;
        $collectionOrder = $this->collectionOrderService->statisticsOrderNumberOfWeek($user->id);
        return $this->success($collectionOrder);
    }

    #[GetMapping('/weekOrder/disbursement_order_num')]
    public function weekOrderDisbursementOrderNum(Request $request): Response
    {
        $user = $request->user;
        $disbursementOrder = $this->disbursementOrderService->statisticsOrderNumberOfWeek($user->id);
        return $this->success($disbursementOrder);
    }

    #[GetMapping('/weekOrder/collection_successful_num')]
    public function weekOrderCollectionSuccessfulNum(Request $request): Response
    {
        $user = $request->user;
        $collectionOrder = $this->collectionOrderService->statisticsOrderSuccessfulNumberOfWeek($user->id);
        return $this->success($collectionOrder);
    }

    #[GetMapping('/weekOrder/disbursement_successful_num')]
    public function weekOrderDisbursementSuccessfulNum(Request $request): Response
    {
        $user = $request->user;
        $disbursementOrder = $this->disbursementOrderService->statisticsOrderSuccessfulNumberOfWeek($user->id);
        return $this->success($disbursementOrder);
    }

    #[GetMapping('/weekOrder/collection_successful_amount')]
    public function weekOrderCollectionSuccessfulAmount(Request $request): Response
    {
        $user = $request->user;
        $collectionOrder = $this->collectionOrderService->statisticsOrderSuccessfulAmountOfWeek($user->id);
        return $this->success($collectionOrder);
    }

    #[GetMapping('/weekOrder/disbursement_successful_amount')]
    public function weekOrderDisbursementSuccessfulAmount(Request $request): Response
    {
        $user = $request->user;
        $disbursementOrder = $this->disbursementOrderService->statisticsOrderSuccessfulAmountOfWeek($user->id);
        return $this->success($disbursementOrder);
    }

    #[GetMapping('/weekOrder/collection_successful_rate')]
    public function weekOrderCollectionSuccessfulRate(Request $request): Response
    {
        $user = $request->user;
        $collectionOrderNumber = $this->collectionOrderService->statisticsOrderNumberOfWeek($user->id);
        $collectionOrderSuccessfulNumber = $this->collectionOrderService->statisticsOrderSuccessfulNumberOfWeek($user->id);
        if ($collectionOrderNumber['count'] > 0) {
            $collectionOrderSuccessfulRate['count'] = bcmul(bcdiv((string)$collectionOrderSuccessfulNumber['count'], (string)$collectionOrderNumber['count'], 4), '100', 2);
        } else {
            $collectionOrderSuccessfulRate['count'] = 0;
        }

        if ($collectionOrderNumber['yesterday'] > 0) {
            $collectionOrderSuccessfulRate['yesterday'] = bcmul(bcdiv((string)$collectionOrderSuccessfulNumber['yesterday'], (string)$collectionOrderNumber['yesterday'], 4), '100', 2);
        } else {
            $collectionOrderSuccessfulRate['yesterday'] = 0;
        }

        $collectionOrderSuccessfulRate['growth'] = bcsub($collectionOrderSuccessfulRate['count'], $collectionOrderSuccessfulRate['yesterday'], 2);
        foreach ($collectionOrderNumber['chartData'] as $key => $value) {
            if ($value['y'] > 0) {
                $itemY = bcmul(bcdiv((string)$collectionOrderSuccessfulNumber['chartData'][$key]['y'], (string)$value['y'], 4), '100', 2);
            } else {
                $itemY = 0;
            }

            $collectionOrderSuccessfulRate['chartData'][$key] = [
                'x'    => $value['x'],
                'y'    => $itemY,
                'name' => '%'
            ];
        }
        return $this->success($collectionOrderSuccessfulRate);
    }

    #[GetMapping('/weekOrder/disbursement_successful_rate')]
    public function weekOrderDisbursementSuccessfulRate(Request $request): Response
    {
        $user = $request->user;
        $disbursementOrderNumber = $this->disbursementOrderService->statisticsOrderNumberOfWeek($user->id);
        $disbursementOrderSuccessfulNumber = $this->disbursementOrderService->statisticsOrderSuccessfulNumberOfWeek($user->id);
        if ($disbursementOrderNumber['count']) {
            $disbursementOrderSuccessfulRate['count'] = bcmul(bcdiv((string)$disbursementOrderSuccessfulNumber['count'], (string)$disbursementOrderNumber['count'], 4), '100', 2);
        } else {
            $disbursementOrderSuccessfulRate['count'] = 0;
        }
        if ($disbursementOrderNumber['yesterday'] > 0) {
            $disbursementOrderSuccessfulRate['yesterday'] = bcmul(bcdiv((string)$disbursementOrderSuccessfulNumber['yesterday'], (string)$disbursementOrderNumber['yesterday'], 4), '100', 2);
        } else {
            $disbursementOrderSuccessfulRate['yesterday'] = 0;
        }


        $disbursementOrderSuccessfulRate['growth'] = bcsub($disbursementOrderSuccessfulRate['count'], $disbursementOrderSuccessfulRate['yesterday'], 2);
        foreach ($disbursementOrderNumber['chartData'] as $key => $value) {
            if ($value['y'] > 0) {
                $itemY = bcmul(bcdiv((string)$disbursementOrderSuccessfulNumber['chartData'][$key]['y'], (string)$value['y'], 4), '100', 2);
            } else {
                $itemY = 0;
            }
            $disbursementOrderSuccessfulRate['chartData'][$key] = [
                'x'    => $value['x'],
                'y'    => $itemY,
                'name' => '%'
            ];
        }
        return $this->success($disbursementOrderSuccessfulRate);
    }


    // 按小时统计支付成功订单数量
    #[GetMapping('/successOrder/hourToday')]
    public function getSuccessOrderCountByHourToday(Request $request): Response
    {
        $user = $request->user;
        $collectionOrder = $this->collectionOrderService->getSuccessOrderCountByHourToday($user->id);
        $disbursementOrder = $this->disbursementOrderService->getSuccessOrderCountByHourToday($user->id);
        $queryCollectionHourList = array_column($collectionOrder, 'order_count', 'pay_time_hour');
        $queryDisbursementHourList = array_column($disbursementOrder, 'order_count', 'pay_time_hour');
        // 计算取$startDate 和 $endDate 之间的所有小时数 YmdH （2025083013） date('Y-m-d', strtotime('-7 day')), date('Y-m-d')
        $startDate = date('Y-m-d');
        $endDate = date('Y-m-d', strtotime('+1 day'));
        $hourCount = (strtotime($endDate) - strtotime($startDate)) / 3600;
        $hourCollectionList = [];
        $hourDisbursementList = [];
        for ($i = 0; $i <= $hourCount; $i++) {
            $hour = date('YmdH', strtotime($startDate) + $i * 3600);
            $hourCollectionList[$hour] = $queryCollectionHourList[$hour] ?? 0;
            $hourDisbursementList[$hour] = $queryDisbursementHourList[$hour] ?? 0;
        }

        // $xAxis 取$hourList所有key最后两位（小时数）
        $xAxis = array_map(static function ($item) {
            return substr($item, -2) . ':00';
        }, array_keys($hourCollectionList));
        return $this->success([
            'xAxis' => $xAxis,
            'data'  => [
                [
                    'name'  => 'collection',
                    'value' => array_values($hourCollectionList)
                ],
                [
                    'name'  => 'disbursement',
                    'value' => array_values($hourDisbursementList)
                ]
            ]
        ]);
    }

    #[GetMapping('/successOrder/hourYesterday')]
    public function getSuccessOrderCountByHourYesterday(Request $request): Response
    {
        $user = $request->user;
        $collectionOrder = $this->collectionOrderService->getSuccessOrderCountByHourYesterday($user->id);
        $disbursementOrder = $this->disbursementOrderService->getSuccessOrderCountByHourYesterday($user->id);
        $queryCollectionHourList = array_column($collectionOrder, 'order_count', 'pay_time_hour');
        $queryDisbursementHourList = array_column($disbursementOrder, 'order_count', 'pay_time_hour');
        // 计算取$startDate 和 $endDate 之间的所有小时数 YmdH （2025083013） date('Y-m-d', strtotime('-7 day')), date('Y-m-d')
        $startDate = date('Y-m-d', strtotime('-1 day'));
        $endDate = date('Y-m-d');
        $hourCount = (strtotime($endDate) - strtotime($startDate)) / 3600;
        $hourCollectionList = [];
        $hourDisbursementList = [];
        for ($i = 0; $i <= $hourCount; $i++) {
            $hour = date('YmdH', strtotime($startDate) + $i * 3600);
            $hourCollectionList[$hour] = $queryCollectionHourList[$hour] ?? 0;
            $hourDisbursementList[$hour] = $queryDisbursementHourList[$hour] ?? 0;
        }

        // $xAxis 取$hourList所有key最后两位（小时数）
        $xAxis = array_map(static function ($item) {
            return substr($item, -2) . ':00';
        }, array_keys($hourCollectionList));
        return $this->success([
            'xAxis' => $xAxis,
            'data'  => [
                [
                    'name'  => 'collection',
                    'value' => array_values($hourCollectionList)
                ],
                [
                    'name'  => 'disbursement',
                    'value' => array_values($hourDisbursementList)
                ]
            ]
        ]);
    }

    #[GetMapping('/successOrder/hourWeek')]
    public function getSuccessOrderCountByHourWeek(Request $request): Response
    {
        $user = $request->user;
        $collectionOrder = $this->collectionOrderService->getSuccessOrderCountByHourWeek($user->id);
        $disbursementOrder = $this->disbursementOrderService->getSuccessOrderCountByHourWeek($user->id);
        $queryCollectionHourList = array_column($collectionOrder, 'order_count', 'pay_time_hour');
        $queryDisbursementHourList = array_column($disbursementOrder, 'order_count', 'pay_time_hour');
        // 计算取$startDate 和 $endDate 之间的所有小时数 YmdH （2025083013） date('Y-m-d', strtotime('-7 day')), date('Y-m-d')
        $startDate = date('Y-m-d', strtotime('-7 day'));
        $endDate = date('Y-m-d', strtotime('+1 day'));
        $hourCount = (strtotime($endDate) - strtotime($startDate)) / 3600;
        $hourCollectionList = [];
        $hourDisbursementList = [];
        for ($i = 0; $i <= $hourCount; $i++) {
            $hour = date('YmdH', strtotime($startDate) + $i * 3600);
            $hourCollectionList[$hour] = $queryCollectionHourList[$hour] ?? 0;
            $hourDisbursementList[$hour] = $queryDisbursementHourList[$hour] ?? 0;
        }

        // $xAxis 取$hourList所有key最后两位（小时数）
        $xAxis = array_map(static function ($item) {
            return substr($item, -2) . ':00';
        }, array_keys($hourCollectionList));
        return $this->success([
            'xAxis' => $xAxis,
            'data'  => [
                [
                    'name'  => 'collection',
                    'value' => array_values($hourCollectionList)
                ],
                [
                    'name'  => 'disbursement',
                    'value' => array_values($hourDisbursementList)
                ]
            ]
        ]);
    }


    // 统计近10天当前用户每天登陆次数
    #[GetMapping('/login_times')]
    public function getUserLoginTimes(Request $request): Response
    {
        $user = $request->user;
        $loginStats = $this->userLoginLogService->statisticsLoginCountOfLast10Days($user->username);
        return $this->success($loginStats);
    }

}