<?php

namespace app\constants;

use app\constants\lib\ConstantsOptionTrait;

class BankDisbursementUpload
{
    use ConstantsOptionTrait;

    // parsing_status 解析状态：0失败，1成功
    public const PARSING_STATUS_FAIL = 0;
    public const PARSING_STATUS_SUCCESS = 1;
    public static array $parsing_status_list = [
        self::PARSING_STATUS_FAIL    => 'bank_disbursement_upload.enums.parsing_status.fail',
        self::PARSING_STATUS_SUCCESS => 'bank_disbursement_upload.enums.parsing_status.success',
    ];
}