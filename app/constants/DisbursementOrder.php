<?php

namespace app\constants;

use app\constants\lib\ConstantsOptionTrait;

class DisbursementOrder
{
    use ConstantsOptionTrait;

    // 订单状态:  0-创建中 1-已创建 2-已分配 10-待支付 11-待对账 20-成功 30-挂起   40-失败 41-已取消 43-已失效 44-已退款
    public const STATUS_CREATING = 0;
    public const STATUS_CREATED = 1;
    public const STATUS_ALLOCATED = 2;
    public const STATUS_WAIT_PAY = 10;
    public const STATUS_WAIT_FILL = 11;
    public const STATUS_SUCCESS = 20;
    public const STATUS_SUSPEND = 30;
    public const STATUS_FAIL = 40;
    public const STATUS_CANCEL = 41;
    public const STATUS_INVALID = 43;
    public const AdjustToFailure = 44;
    public static array $status_list = [
        self::STATUS_CREATING  => 'disbursement_order.enums.status.0',
        self::STATUS_CREATED   => 'disbursement_order.enums.status.1',
        self::STATUS_ALLOCATED => 'disbursement_order.enums.status.2',
        self::STATUS_WAIT_PAY  => 'disbursement_order.enums.status.10',
        self::STATUS_WAIT_FILL => 'disbursement_order.enums.status.11',
        self::STATUS_SUCCESS   => 'disbursement_order.enums.status.20',
        self::STATUS_SUSPEND   => 'disbursement_order.enums.status.30',
        self::STATUS_FAIL      => 'disbursement_order.enums.status.40',
        self::STATUS_CANCEL    => 'disbursement_order.enums.status.41',
        self::STATUS_INVALID   => 'disbursement_order.enums.status.43',
        self::AdjustToFailure  => 'disbursement_order.enums.status.44',
    ];

    // `channel_type` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '渠道类型：1-银行 2-上游第三方',
    public const CHANNEL_TYPE_BANK = 1;
    public const CHANNEL_TYPE_UPSTREAM = 2;
    public static array $channel_type_list = [
        self::CHANNEL_TYPE_BANK     => 'disbursement_order.enums.channel_type.bank',
        self::CHANNEL_TYPE_UPSTREAM => 'disbursement_order.enums.channel_type.upstream',
    ];

    //  `payment_type` tinyint(2) NOT NULL COMMENT '付款类型:1-银行卡 2-UPI',
    public const PAYMENT_TYPE_BANK_CARD = 1;
    public const PAYMENT_TYPE_UPI = 2;
    public static array $payment_type_list = [
        self::PAYMENT_TYPE_BANK_CARD => 'disbursement_order.enums.payment_type.bank_card',
        self::PAYMENT_TYPE_UPI       => 'disbursement_order.enums.payment_type.upi',
    ];

    // notify_status 通知状态:0-未通知 1-回调中 2-通知成功 3-通知失败
    public const NOTIFY_STATUS_NOT_NOTIFY = 0;

    public const NOTIFY_STATUS_CALLBACK_ING = 1;
    public const NOTIFY_STATUS_NOTIFY_SUCCESS = 2;
    public const NOTIFY_STATUS_NOTIFY_FAIL = 3;

    public static array $notify_status_list = [
        self::NOTIFY_STATUS_NOT_NOTIFY     => 'disbursement_order.enums.notify_status.not_notify',
        self::NOTIFY_STATUS_NOTIFY_SUCCESS => 'disbursement_order.enums.notify_status.notify_success',
        self::NOTIFY_STATUS_NOTIFY_FAIL    => 'disbursement_order.enums.notify_status.notify_fail',
        self::NOTIFY_STATUS_CALLBACK_ING   => 'disbursement_order.enums.notify_status.callback_ing',
    ];

    // 账单模板
    const BILL_TEMPLATE_ICICI        = 'icici';
    const BILL_TEMPLATE_ICICI2       = 'icici2';
    const BILL_TEMPLATE_BANDHAN      = 'bandhan';
    const BILL_TEMPLATE_YES_MSME     = 'yesmsme';
    const BILL_TEMPLATE_AXIS         = 'axis';
    const BILL_TEMPLATE_AXIS_NEFT    = 'axisneft';
    const BILL_TEMPLATE_AXIS_NEO     = 'axisneo';
    const BILL_TEMPLATE_IDFC         = 'idfc';
    const BILL_TEMPLATE_IOB_SAME     = 'iobsamebank';
    const BILL_TEMPLATE_IOB_OTHER    = 'iobotherbank';

    public static array $bill_template_list = [
        self::BILL_TEMPLATE_ICICI        =>  'disbursement_order.enums.bill_template.icici',
        self::BILL_TEMPLATE_ICICI2       =>  'disbursement_order.enums.bill_template.icici2',
        self::BILL_TEMPLATE_BANDHAN      =>  'disbursement_order.enums.bill_template.bandhan',
        self::BILL_TEMPLATE_YES_MSME     =>  'disbursement_order.enums.bill_template.yesmsme',
        self::BILL_TEMPLATE_AXIS         =>  'disbursement_order.enums.bill_template.axis',
        self::BILL_TEMPLATE_AXIS_NEFT    =>  'disbursement_order.enums.bill_template.axisneft',
        self::BILL_TEMPLATE_AXIS_NEO     =>  'disbursement_order.enums.bill_template.axisneo',
        self::BILL_TEMPLATE_IDFC         =>  'disbursement_order.enums.bill_template.idfc',
        self::BILL_TEMPLATE_IOB_SAME     =>  'disbursement_order.enums.bill_template.iobsamebank',
        self::BILL_TEMPLATE_IOB_OTHER    =>  'disbursement_order.enums.bill_template.iobotherbank',
    ];

    public const DISBURSEMENT_ORDER_WRITE_OFF_QUEUE_NAME = 'disbursement-order-write-off-consumer';
    public const DISBURSEMENT_ORDER_REFUND_QUEUE_NAME = 'disbursement-order-refund-consumer';

}