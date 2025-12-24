<?php

namespace app\constants;

use app\constants\lib\ConstantsOptionTrait;

class DisbursementOrderUpstreamCreateQueue
{
    use ConstantsOptionTrait;

    // 队列名称
    public const CONSUMER_QUEUE_NAME = 'disbursement-order-upstream-create-consumer';

    // 处理状态:0-待处理 1-处理中 2-成功 3-失败
    public const PROCESS_STATUS_WAIT = 0;
    public const PROCESS_STATUS_PROCESSING = 1;
    public const PROCESS_STATUS_SUCCESS = 2;
    public const PROCESS_STATUS_FAIL = 3;
    public static array $process_status_list = [
        self::PROCESS_STATUS_WAIT       => 'disbursement_order_upstream_create_queue.enums.process_status.wait',
        self::PROCESS_STATUS_PROCESSING => 'disbursement_order_upstream_create_queue.enums.process_status.processing',
        self::PROCESS_STATUS_SUCCESS    => 'disbursement_order_upstream_create_queue.enums.process_status.success',
        self::PROCESS_STATUS_FAIL       => 'disbursement_order_upstream_create_queue.enums.process_status.fail',
    ];

    // 付款类型:1-银行卡 2-UPI
    public const PAYMENT_TYPE_BANK_CARD = 1;
    public const PAYMENT_TYPE_UPI = 2;
    public static array $payment_type_list = [
        self::PAYMENT_TYPE_BANK_CARD => 'disbursement_order_upstream_create_queue.enums.payment_type.bank_card',
        self::PAYMENT_TYPE_UPI       => 'disbursement_order_upstream_create_queue.enums.payment_type.upi',
    ];

    // 默认最大重试次数
    public const DEFAULT_MAX_RETRY_COUNT = 1;

    // 重试间隔（分钟）
    public const RETRY_INTERVALS = [
        1 => 2,   // 第1次重试：2分钟后
        2 => 4,   // 第2次重试：4分钟后
        3 => 8,   // 第3次重试：8分钟后
    ];
}