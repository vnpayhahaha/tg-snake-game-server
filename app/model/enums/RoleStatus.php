<?php

namespace app\model\enums;

use app\constants\Role;
use app\lib\annotation\Message;
use app\lib\traits\ConstantsTrait;

enum RoleStatus: int
{
    use ConstantsTrait;

    #[Message('role.enums.status.1')]
    case Normal = Role::STATUS_NORMAL;

    #[Message('role.enums.status.2')]
    case DISABLE = Role::STATUS_DISABLE;

    public function isNormal(): bool
    {
        return $this === self::Normal;
    }

    public function isDisable(): bool
    {
        return $this === self::DISABLE;
    }
}
