<?php

namespace app\constants;

use app\constants\lib\ConstantsOptionTrait;

class TransactionRawData
{
    use ConstantsOptionTrait;

    // 状态：0未解析 1解析成功 2解析失败'
    public const STATUS_NOT_PARSED = 0;
    public const STATUS_PARSED_SUCCESS = 1;
    public const STATUS_PARSED_FAIL = 2;
    public static array $status_list = [
        self::STATUS_NOT_PARSED     => 'transaction_raw_data.enums.status.not_parsed',
        self::STATUS_PARSED_SUCCESS => 'transaction_raw_data.enums.status.parsed_success',
        self::STATUS_PARSED_FAIL    => 'transaction_raw_data.enums.status.parsed_fail',
    ];

    public const TRANSACTION_RAW_DATA_QUEUE_NAME = 'transaction-raw-data-queue';
}