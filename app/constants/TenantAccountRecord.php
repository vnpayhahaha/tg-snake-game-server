<?php

namespace app\constants;

use app\constants\lib\ConstantsOptionTrait;

class TenantAccountRecord
{
    use ConstantsOptionTrait;

    // 变更类型：1-订单交易 2-订单退款 3-人工加帐 4-人工减帐 5-冻结 6-解冻 7-转入 8-转出 9-冲正 10-反转 11-调整差错
    public const CHANGE_TYPE_TRANSACTION  = 1;
    public const CHANGE_TYPE_REFUND       = 2;
    public const CHANGE_TYPE_MANUAL_ADD   = 3;
    public const CHANGE_TYPE_MANUAL_SUB   = 4;
    public const CHANGE_TYPE_FREEZE       = 5;
    public const CHANGE_TYPE_UNFREEZE     = 6;
    public const CHANGE_TYPE_TRANSFER_IN  = 7;
    public const CHANGE_TYPE_TRANSFER_OUT = 8;
    public const CHANGE_TYPE_REVERSE      = 9;
    public const CHANGE_TYPE_REVERSAL     = 10;
    public const CHANGE_TYPE_ERROR_ADJUST = 11;

    public static array $change_type_list = [
        self::CHANGE_TYPE_TRANSACTION  => 'tenant_account_record.enums.change_type.transaction',
        self::CHANGE_TYPE_REFUND       => 'tenant_account_record.enums.change_type.refund',
        self::CHANGE_TYPE_MANUAL_ADD   => 'tenant_account_record.enums.change_type.manual_add',
        self::CHANGE_TYPE_MANUAL_SUB   => 'tenant_account_record.enums.change_type.manual_sub',
        self::CHANGE_TYPE_FREEZE       => 'tenant_account_record.enums.change_type.freeze',
        self::CHANGE_TYPE_UNFREEZE     => 'tenant_account_record.enums.change_type.unfreeze',
        self::CHANGE_TYPE_TRANSFER_IN  => 'tenant_account_record.enums.change_type.transfer_in',
        self::CHANGE_TYPE_TRANSFER_OUT => 'tenant_account_record.enums.change_type.transfer_out',
        self::CHANGE_TYPE_REVERSE      => 'tenant_account_record.enums.change_type.reverse',
        self::CHANGE_TYPE_REVERSAL     => 'tenant_account_record.enums.change_type.reversal',
        self::CHANGE_TYPE_ERROR_ADJUST => 'tenant_account_record.enums.change_type.error_adjust',
    ];

}
