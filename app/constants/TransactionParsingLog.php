<?php

namespace app\constants;

use app\constants\lib\ConstantsOptionTrait;

class TransactionParsingLog
{
    use ConstantsOptionTrait;
    // 状态：1解析成功 2失败或部分失败
    public const STATUS_SUCCESS = 1;
    public const STATUS_FAIL = 2;
    public static array $status_list = [
        self::STATUS_SUCCESS => 'transaction_parsing_log.enums.status.success',
        self::STATUS_FAIL    => 'transaction_parsing_log.enums.status.fail',
    ];

}