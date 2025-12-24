<?php

namespace app\constants;

use app\constants\lib\ConstantsOptionTrait;

class TransactionRecord
{
    use ConstantsOptionTrait;

    // 业务交易类型：10-订单交易 11-订单退款 20-人工加帐 21-人工减帐 23-冻结 24-解冻 30-收转付 31-付转收 40-冲正 41-调整差错
    public const TYPE_ORDER_TRANSACTION       = 10;
    public const TYPE_ORDER_REFUND            = 11;
    public const TYPE_MANUAL_ADD              = 20;
    public const TYPE_MANUAL_SUB              = 21;
    public const TYPE_FREEZE                  = 23;
    public const TYPE_UNFREEZE                = 24;
    public const TYPE_TRANSFER_RECEIVE_TO_PAY = 30;
    public const TYPE_TRANSFER_PAY_TO_RECEIVE = 31;
    public const TYPE_REVERSE                 = 40;
    public const TYPE_ERROR_ADJUST            = 50;
    public static array $type_list = [
        self::TYPE_ORDER_TRANSACTION       => 'transaction_record.enums.type.order_transaction',
        self::TYPE_ORDER_REFUND            => 'transaction_record.enums.type.order_refund',
        self::TYPE_MANUAL_ADD              => 'transaction_record.enums.type.manual_add',
        self::TYPE_MANUAL_SUB              => 'transaction_record.enums.type.manual_sub',
        self::TYPE_FREEZE                  => 'transaction_record.enums.type.freeze',
        self::TYPE_UNFREEZE                => 'transaction_record.enums.type.unfreeze',
        self::TYPE_TRANSFER_RECEIVE_TO_PAY => 'transaction_record.enums.type.transfer_receive_to_pay',
        self::TYPE_TRANSFER_PAY_TO_RECEIVE => 'transaction_record.enums.type.transfer_pay_to_receive',
        self::TYPE_REVERSE                 => 'transaction_record.enums.type.reverse',
        self::TYPE_ERROR_ADJUST            => 'transaction_record.enums.type.error_adjust',

    ];


    // 交易状态:0-等待结算 1-处理中 2-撤销 3-成功 4-失败
    public const STATUS_WAITING_SETTLEMENT = 0;
    public const STATUS_PROCESSING         = 1;
    public const STATUS_CANCEL             = 2;
    public const STATUS_SUCCESS            = 3;
    public const STATUS_FAIL               = 4;
    public static array $status_list = [
        self::STATUS_WAITING_SETTLEMENT => 'transaction_record.enums.status.waiting_settlement',
        self::STATUS_PROCESSING         => 'transaction_record.enums.status.processing',
        self::STATUS_CANCEL             => 'transaction_record.enums.status.cancel',
        self::STATUS_SUCCESS            => 'transaction_record.enums.status.success',
        self::STATUS_FAIL               => 'transaction_record.enums.status.fail',
    ];

    // 延迟模式:1-D0(立即) 2-D(自然日) 3-T(工作日)
    public const SETTLEMENT_DELAY_MODE_D0    = 1;
    public const SETTLEMENT_DELAY_MODE_DAY   = 2;
    public const SETTLEMENT_DELAY_MODE_TRADE = 3;
    public static array $settlement_delay_mode_list = [
        self::SETTLEMENT_DELAY_MODE_D0    => 'transaction_record.enums.settlement_delay_mode.d0',
        self::SETTLEMENT_DELAY_MODE_DAY   => 'transaction_record.enums.settlement_delay_mode.day',
        self::SETTLEMENT_DELAY_MODE_TRADE => 'transaction_record.enums.settlement_delay_mode.trade',
    ];

    // 节假日调整:0-不调整 1-顺延 2-提前
    public const HOLIDAY_ADJUSTMENT_NONE     = 0;
    public const HOLIDAY_ADJUSTMENT_POSTPONE = 1;
    public const HOLIDAY_ADJUSTMENT_ADVANCE  = 2;
    public static array $holiday_adjustment_list = [
        self::HOLIDAY_ADJUSTMENT_NONE     => 'transaction_record.enums.holiday_adjustment.none',
        self::HOLIDAY_ADJUSTMENT_POSTPONE => 'transaction_record.enums.holiday_adjustment.postpone',
        self::HOLIDAY_ADJUSTMENT_ADVANCE  => 'transaction_record.enums.holiday_adjustment.advance',
    ];
}
