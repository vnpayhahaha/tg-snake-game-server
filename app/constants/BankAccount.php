<?php

namespace app\constants;

use app\constants\lib\ConstantsOptionTrait;

class BankAccount
{
    use ConstantsOptionTrait;

    // $status 状态:1-启用 2-停用
    public const STATUS_ENABLE  = 1;
    public const STATUS_DISABLE = 2;
    public static array $status_list = [
        self::STATUS_ENABLE  => 'bank_account.enums.status.1',
        self::STATUS_DISABLE => 'bank_account.enums.status.2',
    ];

    // $support_collection 支持代收
    public const SUPPORT_COLLECTION_YES = 1;
    public const SUPPORT_COLLECTION_NO  = 2;
    public static array $support_collection_list = [
        self::SUPPORT_COLLECTION_YES => 'bank_account.enums.support_collection.1',
        self::SUPPORT_COLLECTION_NO  => 'bank_account.enums.support_collection.2',
    ];

    // $support_disbursement 支持代付
    public const SUPPORT_DISBURSEMENT_YES = 1;
    public const SUPPORT_DISBURSEMENT_NO  = 2;
    public static array $support_disbursement_list = [
        self::SUPPORT_DISBURSEMENT_YES => 'bank_account.enums.support_disbursement.1',
        self::SUPPORT_DISBURSEMENT_NO  => 'bank_account.enums.support_disbursement.2',
    ];
}
