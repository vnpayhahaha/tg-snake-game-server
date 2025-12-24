<?php

namespace app\constants;

use app\constants\lib\ConstantsOptionTrait;

class TenantApp
{
    use ConstantsOptionTrait;

    // 启用状态(1正常 0停用)
    public const STATUS_NORMAL  = 1;
    public const STATUS_DISABLE = 0;
    public static array $status_list = [
        self::STATUS_NORMAL  => 'tenant_app.enums.status.1',
        self::STATUS_DISABLE => 'tenant_app.enums.status.2',
    ];

}
