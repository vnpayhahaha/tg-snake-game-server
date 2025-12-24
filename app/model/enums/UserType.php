<?php

namespace app\model\enums;

use app\constants\User;
use app\lib\traits\ConstantsTrait;
use app\lib\annotation\Message;

enum UserType: int
{
    use ConstantsTrait;

    #[Message('user.enums.type.100')]
    case SYSTEM = User::TYPE_SYSTEM;

    #[Message('user.enums.type.200')]
    case Guest = User::TYPE_GUEST;
}
