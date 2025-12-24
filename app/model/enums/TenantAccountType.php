<?php

namespace app\model\enums;

use app\constants\TenantAccount;
use app\lib\traits\ConstantsTrait;

enum TenantAccountType: int
{
    use ConstantsTrait;

    case Receive = TenantAccount::ACCOUNT_TYPE_RECEIVE;
    case Pay = TenantAccount::ACCOUNT_TYPE_PAY;

    public function isReceive(): bool
    {
        return $this === self::Receive;
    }

    public function isPay(): bool
    {
        return $this === self::Pay;
    }
}
