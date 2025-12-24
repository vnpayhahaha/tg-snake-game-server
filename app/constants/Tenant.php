<?php

namespace app\constants;

use app\constants\lib\ConstantsOptionTrait;

class Tenant
{
    use ConstantsOptionTrait;

    // 启用状态(1正常 0停用)
    public const STATUS_NORMAL  = 1;
    public const STATUS_DISABLE = 2;
    public static array $status_list = [
        self::STATUS_NORMAL  => 'tenant.enums.is_enabled.1',
        self::STATUS_DISABLE => 'tenant.enums.is_enabled.2',
    ];

    // 收款结算方式(1实际金额 2订单金额)
    public const SETTLEMENT_ACTUAL_AMOUNT = 1;
    public const SETTLEMENT_ORDER_AMOUNT  = 2;
    public static array $settlement_list = [
        self::SETTLEMENT_ACTUAL_AMOUNT => 'tenant.enums.settlement.1',
        self::SETTLEMENT_ORDER_AMOUNT  => 'tenant.enums.settlement.2',
    ];

    // 银行卡获取方式(1随机 2依次 3轮询)
    public const BANK_CARD_RANDOM     = 1;
    public const BANK_CARD_SEQUENTIAL = 2;
    public const BANK_CARD_POLLING    = 3;
    public static array $bank_card_list = [
        self::BANK_CARD_RANDOM     => 'tenant.enums.bank_card.1',
        self::BANK_CARD_SEQUENTIAL => 'tenant.enums.bank_card.2',
        self::BANK_CARD_POLLING    => 'tenant.enums.bank_card.3',
    ];

    // $settlement_delay_mode 入账类型(1:D0 2:D 3:T)
    public const SETTLEMENT_DELAY_MODE_D0 = 1;
    public const SETTLEMENT_DELAY_MODE_D  = 2;
    public const SETTLEMENT_DELAY_MODE_T  = 3;
    public static array $settlement_delay_mode_list = [
        self::SETTLEMENT_DELAY_MODE_D0 => 'D0',
        self::SETTLEMENT_DELAY_MODE_D  => 'D',
        self::SETTLEMENT_DELAY_MODE_T  => 'T',
    ];

    // 上游第三方收款项 upstream_options
    public static array $upstream_options = [
        'aipay'  => \app\upstream\aipay\CollectionService::class,
        'caipay' => \app\upstream\caipay\CollectionService::class,
    ];

    public static array $upstream_disbursement_options = [
        'aipay'  => \app\upstream\aipay\DisbursementService::class,
        'caipay' => \app\upstream\caipay\DisbursementService::class,
    ];

    // payment_assign_options
    public static array $payment_assign_options = [

    ];

    // collection_use_method 收款使用方法1银行账户 2上游
    public const COLLECTION_USE_METHOD_BANK_ACCOUNT = 1;
    public const COLLECTION_USE_METHOD_UPSTREAM     = 2;
    public static array $collection_use_method_list = [
        self::COLLECTION_USE_METHOD_BANK_ACCOUNT => 'tenant.enums.collection_use_method.bank_account',
        self::COLLECTION_USE_METHOD_UPSTREAM     => 'tenant.enums.collection_use_method.upstream',
    ];

    // receipt_fixed_fee  收款手续费类型(1固定 2费率)
    public const RECEIPT_FEE_TYPE_FIXED = 1;
    public const RECEIPT_FEE_TYPE_RATE  = 2;
    public static array $receipt_fee_type_list = [
        self::RECEIPT_FEE_TYPE_FIXED => 'tenant.enums.receipt_fee_type.fixed',
        self::RECEIPT_FEE_TYPE_RATE  => 'tenant.enums.receipt_fee_type.rate',
    ];

    // payment_fee_type 付款手续费类型(1固定 2费率)
    public const PAYMENT_FEE_TYPE_FIXED = 1;
    public const PAYMENT_FEE_TYPE_RATE  = 2;
    public static array $payment_fee_type_list = [
        self::PAYMENT_FEE_TYPE_FIXED => 'tenant.enums.payment_fee_type.fixed',
        self::PAYMENT_FEE_TYPE_RATE  => 'tenant.enums.payment_fee_type.rate',
    ];

    // 收银台页面模板
    public const CASHIER_TEMPLATE_DEFAULT = 0;
    public const CASHIER_TEMPLATE_CUSTOM1  = 1;
    public const CASHIER_TEMPLATE_CUSTOM2  = 2;
    public static array $cashier_template_list = [
        self::CASHIER_TEMPLATE_DEFAULT => 'QrBaseDefault',
        self::CASHIER_TEMPLATE_CUSTOM1  => 'QrBaseCashdesk',
        self::CASHIER_TEMPLATE_CUSTOM2  => 'QrBasePPAndPTM',
    ];
}
