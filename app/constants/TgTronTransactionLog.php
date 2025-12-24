<?php

namespace app\constants;

use app\constants\lib\ConstantsOptionTrait;

class TgTronTransactionLog
{
    use ConstantsOptionTrait;

    // 交易类型: 1-入账 2-出账
    public const TRANSACTION_TYPE_INCOMING = 1;
    public const TRANSACTION_TYPE_OUTGOING = 2;
    public static array $transaction_type_list = [
        self::TRANSACTION_TYPE_INCOMING => 'tg_tron_transaction_log.enums.transaction_type.1',
        self::TRANSACTION_TYPE_OUTGOING => 'tg_tron_transaction_log.enums.transaction_type.2',
    ];

    // 是否有效交易: 0-无效 1-有效
    public const VALID_NO = 0;
    public const VALID_YES = 1;
    public static array $valid_list = [
        self::VALID_NO  => 'tg_tron_transaction_log.enums.valid.0',
        self::VALID_YES => 'tg_tron_transaction_log.enums.valid.1',
    ];

    // 是否已处理: 0-未处理 1-已处理
    public const PROCESSED_NO = 0;
    public const PROCESSED_YES = 1;
    public static array $processed_list = [
        self::PROCESSED_NO  => 'tg_tron_transaction_log.enums.processed.0',
        self::PROCESSED_YES => 'tg_tron_transaction_log.enums.processed.1',
    ];

    // TRON 交易状态
    public const TX_STATUS_SUCCESS = 'SUCCESS';
    public const TX_STATUS_FAILED = 'FAILED';
    public const TX_STATUS_PENDING = 'PENDING';

    // 最小有效金额（TRX）
    public const MIN_VALID_AMOUNT = 1;
}
