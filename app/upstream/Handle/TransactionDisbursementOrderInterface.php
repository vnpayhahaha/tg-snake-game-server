<?php

namespace app\upstream\Handle;

use app\model\enums\DisbursementOrderVerificationQueuePayStatus;
use app\model\ModelChannelAccount;
use app\model\ModelDisbursementOrder;
use JetBrains\PhpStorm\ArrayShape;
use support\Request;
use support\Response;

interface TransactionDisbursementOrderInterface
{
    // 初始化配置
    public function init(ModelChannelAccount $channel_account): self;
    // 创建订单

    /**
     * @param ModelDisbursementOrder $orderModel
     * @return array result
     * -- bool ok
     * -- string origin (json原始返回数据)
     * -- array data
     * -- string _upstream_order_no
     */
    #[ArrayShape([
        'ok'     => 'bool',
        'msg'    => 'string',
        'origin' => 'string',
        'data'   => [
            '_upstream_order_no' => 'string',
        ]
    ])]
    public function createOrder(ModelDisbursementOrder $orderModel): array;
    // 查询订单状态
    public function queryOrder(string $platform_order_no, string $upstream_order_no): array;
    // 取消订单
    public function cancelOrder(string $platform_order_no, string $upstream_order_no): bool;
    // 接收通知
    /**
     * 回调通知
     * @return array result
     *   -- bool ok
     *   -- string origin (json原始返回数据)
     *   -- array data
     *      -- string _upstream_order_no
     *      -- string _platform_order_no
     *      -- float _amount
     *      -- string _pay_time
     *      -- string _utr
     *      -- DisbursementOrderVerificationQueuePayStatus _payment_status
     *      -- string _rejection_reason
     */
    #[ArrayShape([
        'ok'     => 'bool',
        'origin' => 'string',
        'data'   => [
            '_upstream_order_no' => 'string',
            '_platform_order_no' => 'string',
            '_amount'            => 'float',
            '_pay_time'          => 'string',
            '_utr'               => 'string',
            '_payment_status'    => DisbursementOrderVerificationQueuePayStatus::class,
            '_rejection_reason'  => 'string',
        ]
    ])]
    public function notify(Request $request): array;

    public function notifyReturn(bool $success): Response;
}
