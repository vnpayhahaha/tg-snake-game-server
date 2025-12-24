<?php

namespace app\model\enums;

use app\constants\User;
use app\lib\traits\ConstantsTrait;
use app\lib\annotation\Message;

enum UserStatus: int
{
    use ConstantsTrait;

    #[Message('user.enums.status.1')]
    case Normal = User::STATUS_NORMAL;

    #[Message('user.enums.status.2')]
    case DISABLE = User::STATUS_DISABLE;

    public function isNormal(): bool
    {
        return $this === self::Normal;
    }

    public function isDisable(): bool
    {
        return $this === self::DISABLE;
    }
}
