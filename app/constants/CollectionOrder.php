<?php

namespace app\constants;

use app\constants\lib\ConstantsOptionTrait;

class CollectionOrder
{
    use ConstantsOptionTrait;

    const ORDER_FLOAT_AMOUNT_CACHE_KEY = 'order_float_amount_';

    // 订单状态:
    // * 0-创建 10-处理中 20-成功 30-挂起 40-失败 41-已取消 43-已失效 44-已退款
    public const STATUS_CREATE = 0;
    public const STATUS_PROCESSING = 10;
    public const STATUS_SUCCESS = 20;
    public const STATUS_SUSPEND = 30;
    public const STATUS_FAIL = 40;
    public const STATUS_CANCEL = 41;
    public const STATUS_INVALID = 43;
    public const STATUS_REFUND = 44;
    public static array $status_list = [
        self::STATUS_CREATE     => 'collection_order.enums.status.0',
        self::STATUS_PROCESSING => 'collection_order.enums.status.10',
        self::STATUS_SUCCESS    => 'collection_order.enums.status.20',
        self::STATUS_SUSPEND    => 'collection_order.enums.status.30',
        self::STATUS_FAIL       => 'collection_order.enums.status.40',
        self::STATUS_CANCEL     => 'collection_order.enums.status.41',
        self::STATUS_INVALID    => 'collection_order.enums.status.43',
        self::STATUS_REFUND     => 'collection_order.enums.status.44',
    ];

    // 收款类型:1-银行卡 2-UPI 3-第三方支付
    public const COLLECTION_TYPE_BANK_ACCOUNT = 1;
    public const COLLECTION_TYPE_UPI = 2;
    public const COLLECTION_TYPE_UPSTREAM = 3;
    public static array $collection_type_list = [
        self::COLLECTION_TYPE_BANK_ACCOUNT => 'collection_order.enums.collection_type.bank_account',
        self::COLLECTION_TYPE_UPI          => 'collection_order.enums.collection_type.upi',
        self::COLLECTION_TYPE_UPSTREAM     => 'collection_order.enums.collection_type.upstream',
    ];

    // settlement_type 入账结算类型:0-未入账 1-实付金额 2-订单金额
//    public const SETTLEMENT_TYPE_NOT_SETTLED = 0;
    public const SETTLEMENT_TYPE_PAID_AMOUNT = 1;
    public const SETTLEMENT_TYPE_ORDER_AMOUNT = 2;
    public static array $settlement_type_list = [
//        self::SETTLEMENT_TYPE_NOT_SETTLED  => 'collection_order.enums.settlement_type.not_settled',
        self::SETTLEMENT_TYPE_PAID_AMOUNT  => 'collection_order.enums.settlement_type.paid_amount',
        self::SETTLEMENT_TYPE_ORDER_AMOUNT => 'collection_order.enums.settlement_type.order_amount',
    ];

    // recon_type 核销类型:\r\n    0-未核销 \r\n    1-自动核销 \r\n    2-人工核销 \r\n    3-接口核销 \r\n    4-机器人核销
    public const RECON_TYPE_NOT_RECON = 0;
    public const RECON_TYPE_AUTO_RECON = 1;
    public const RECON_TYPE_MANUAL_RECON = 2;
    public const RECON_TYPE_INTERFACE_RECON = 3;
    public const RECON_TYPE_ROBOT_RECON = 4;
    public static array $recon_type_list = [
        self::RECON_TYPE_NOT_RECON       => 'collection_order.enums.recon_type.not_recon',
        self::RECON_TYPE_AUTO_RECON      => 'collection_order.enums.recon_type.auto_recon',
        self::RECON_TYPE_MANUAL_RECON    => 'collection_order.enums.recon_type.manual_recon',
        self::RECON_TYPE_INTERFACE_RECON => 'collection_order.enums.recon_type.interface_recon',
        self::RECON_TYPE_ROBOT_RECON     => 'collection_order.enums.recon_type.robot_recon',
    ];

    // notify_status 通知状态:0-未通知 1-通知成功 2-通知失败 3-回调中
    public const NOTIFY_STATUS_NOT_NOTIFY = 0;

    public const NOTIFY_STATUS_CALLBACK_ING = 1;
    public const NOTIFY_STATUS_NOTIFY_SUCCESS = 2;
    public const NOTIFY_STATUS_NOTIFY_FAIL = 3;

    public static array $notify_status_list = [
        self::NOTIFY_STATUS_NOT_NOTIFY     => 'collection_order.enums.notify_status.not_notify',
        self::NOTIFY_STATUS_NOTIFY_SUCCESS => 'collection_order.enums.notify_status.notify_success',
        self::NOTIFY_STATUS_NOTIFY_FAIL    => 'collection_order.enums.notify_status.notify_fail',
        self::NOTIFY_STATUS_CALLBACK_ING   => 'collection_order.enums.notify_status.callback_ing',
    ];

    public const COLLECTION_ORDER_WRITE_OFF_QUEUE_NAME = 'collection-order-write-off-consumer';
}
