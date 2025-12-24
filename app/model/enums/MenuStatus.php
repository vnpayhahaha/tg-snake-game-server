<?php

namespace app\model\enums;

use app\constants\Menu;
use app\lib\annotation\Message;
use app\lib\traits\ConstantsTrait;

enum MenuStatus: int
{
    use ConstantsTrait;

    #[Message('menu.enums.status.1')]
    case Normal = Menu::STATUS_NORMAL;

    #[Message('menu.enums.status.2')]
    case DISABLE = Menu::STATUS_DISABLE;

    public function isNormal(): bool
    {
        return $this === self::Normal;
    }

    public function isDisable(): bool
    {
        return $this === self::DISABLE;
    }
}
