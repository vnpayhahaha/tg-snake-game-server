<?php

namespace app\model\enums;

use app\constants\Tenant;
use app\lib\traits\ConstantsTrait;

enum TenantStatus: int
{

    use ConstantsTrait;

    case Normal = Tenant::STATUS_NORMAL;
    case DISABLE = Tenant::STATUS_DISABLE;

    public function isNormal(): bool
    {
        return $this === self::Normal;
    }

    public function isDisable(): bool
    {
        return $this === self::DISABLE;
    }
}
