<?php

namespace app\constants;

use app\constants\lib\ConstantsOptionTrait;

class DisbursementOrderVerificationQueue
{
    use ConstantsOptionTrait;

    // 支付状态:0未支付1支付中2支付成功3支付失败
    public const PAY_STATUS_NOT_PAY = 0;
    public const PAY_STATUS_PAYING = 1;
    public const PAY_STATUS_SUCCESS = 2;
    public const PAY_STATUS_FAIL = 3;
    public static array $pay_status_list = [
        self::PAY_STATUS_NOT_PAY => 'disbursement_order_verification_queue.enums.pay_status.not_pay',
        self::PAY_STATUS_PAYING  => 'disbursement_order_verification_queue.enums.pay_status.paying',
        self::PAY_STATUS_SUCCESS => 'disbursement_order_verification_queue.enums.pay_status.success',
        self::PAY_STATUS_FAIL    => 'disbursement_order_verification_queue.enums.pay_status.fail',
    ];

    public const Verification_CONSUMER_QUEUE_NAME = 'disbursement-order-verification-consumer';

    // process_status 处理状态:\r\n    0-待处理 1-处理中 2-成功 3-失败
    public const PROCESS_STATUS_WAIT = 0;
    public const PROCESS_STATUS_PROCESSING = 1;
    public const PROCESS_STATUS_SUCCESS = 2;
    public const PROCESS_STATUS_FAIL = 3;
    public static array $process_status_list = [
        self::PROCESS_STATUS_WAIT       => 'disbursement_order_verification_queue.enums.process_status.wait',
        self::PROCESS_STATUS_PROCESSING => 'disbursement_order_verification_queue.enums.process_status.processing',
        self::PROCESS_STATUS_SUCCESS    => 'disbursement_order_verification_queue.enums.process_status.success',
        self::PROCESS_STATUS_FAIL       => 'disbursement_order_verification_queue.enums.process_status.fail',
    ];
}