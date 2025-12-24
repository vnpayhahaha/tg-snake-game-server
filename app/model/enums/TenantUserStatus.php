<?php

namespace app\model\enums;

use app\constants\TenantUser;
use app\lib\traits\ConstantsTrait;

enum TenantUserStatus: int
{
    use ConstantsTrait;

    // 状态(1正常 2停用)
    case Normal = TenantUser::STATUS_NORMAL;
    case DISABLE = TenantUser::STATUS_DISABLE;

    public function isNormal(): bool
    {
        return $this === self::Normal;
    }

    public function isDisable(): bool
    {
        return $this === self::DISABLE;
    }
}
