<?php

namespace app\model\enums;

use app\constants\TenantApp;
use app\lib\traits\ConstantsTrait;

enum TenantAppStatus: int
{

    use ConstantsTrait;

    case Normal = TenantApp::STATUS_NORMAL;
    case DISABLE = TenantApp::STATUS_DISABLE;

    public function isNormal(): bool
    {
        return $this === self::Normal;
    }

    public function isDisable(): bool
    {
        return $this === self::DISABLE;
    }
}
