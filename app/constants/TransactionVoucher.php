<?php

namespace app\constants;

use app\constants\lib\ConstantsOptionTrait;

class TransactionVoucher
{
    use ConstantsOptionTrait;

    // collection_status 状态(1等待核销 2处理中 3已经核销 4核销失败 5撤销)
    public const COLLECTION_STATUS_WAITING = 1;
    public const COLLECTION_STATUS_PROCESSING = 2;
    public const COLLECTION_STATUS_SUCCESS = 3;
    public const COLLECTION_STATUS_FAIL = 4;
    public const COLLECTION_STATUS_CANCEL = 5;
    public static array $collection_status_list = [
        self::COLLECTION_STATUS_WAITING    => 'transaction_voucher.enums.collection_status.waiting',
        self::COLLECTION_STATUS_PROCESSING => 'transaction_voucher.enums.collection_status.processing',
        self::COLLECTION_STATUS_SUCCESS    => 'transaction_voucher.enums.collection_status.success',
        self::COLLECTION_STATUS_FAIL       => 'transaction_voucher.enums.collection_status.fail',
        self::COLLECTION_STATUS_CANCEL     => 'transaction_voucher.enums.collection_status.cancel',
    ];

    // collection_source 转账凭证来源:0未定义1人工创建2平台内部接口3平台开放下游接口4上游回调接口5银行回执
    public const COLLECTION_SOURCE_UNDEFINED = 0;
    public const COLLECTION_SOURCE_MANUAL = 1;
    public const COLLECTION_SOURCE_INTERNAL = 2;
    public const COLLECTION_SOURCE_OPEN_API = 3;
    public const COLLECTION_SOURCE_UPSTREAM = 4;
    public const COLLECTION_SOURCE_BANK_RECEIPT = 5;
    public static array $collection_source_list = [
        self::COLLECTION_SOURCE_UNDEFINED    => 'transaction_voucher.enums.collection_source.undefined',
        self::COLLECTION_SOURCE_MANUAL       => 'transaction_voucher.enums.collection_source.manual',
        self::COLLECTION_SOURCE_INTERNAL     => 'transaction_voucher.enums.collection_source.internal',
        self::COLLECTION_SOURCE_OPEN_API     => 'transaction_voucher.enums.collection_source.open_api',
        self::COLLECTION_SOURCE_UPSTREAM     => 'transaction_voucher.enums.collection_source.upstream',
        self::COLLECTION_SOURCE_BANK_RECEIPT => 'transaction_voucher.enums.collection_source.bank_receipt',
    ];

    // transaction_voucher_type 转账凭证类型：1utr 2订单id 3平台订单号 platform_order_no 4金额 5上游订单号 upstream_order_no
    public const TRANSACTION_VOUCHER_TYPE_UTR = 1;
    public const TRANSACTION_VOUCHER_TYPE_ORDER_ID = 2;
    public const TRANSACTION_VOUCHER_TYPE_PLATFORM_ORDER_NO = 3;
    public const TRANSACTION_VOUCHER_TYPE_AMOUNT = 4;
    public const TRANSACTION_VOUCHER_TYPE_UPSTREAM_ORDER_NO = 5;
    public static array $transaction_voucher_type_list = [
        self::TRANSACTION_VOUCHER_TYPE_ORDER_ID          => 'transaction_voucher.enums.transaction_voucher_type.order_id',
        self::TRANSACTION_VOUCHER_TYPE_PLATFORM_ORDER_NO => 'transaction_voucher.enums.transaction_voucher_type.platform_order_no',
        self::TRANSACTION_VOUCHER_TYPE_UTR               => 'transaction_voucher.enums.transaction_voucher_type.utr',
        self::TRANSACTION_VOUCHER_TYPE_AMOUNT            => 'transaction_voucher.enums.transaction_voucher_type.amount',
        self::TRANSACTION_VOUCHER_TYPE_UPSTREAM_ORDER_NO => 'transaction_voucher.enums.transaction_voucher_type.upstream_order_no',
    ];

    // transaction_type 交易类型：1代收 2代付
    public const TRANSACTION_TYPE_COLLECTION = 1;
    public const TRANSACTION_TYPE_PAYMENT = 2;
    public static array $transaction_type_list = [
        self::TRANSACTION_TYPE_COLLECTION => 'transaction_voucher.enums.transaction_type.collection',
        self::TRANSACTION_TYPE_PAYMENT    => 'transaction_voucher.enums.transaction_type.payment',
    ];

}
