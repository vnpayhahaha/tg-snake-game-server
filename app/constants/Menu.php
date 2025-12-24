<?php

namespace app\constants;

use app\constants\lib\ConstantsOptionTrait;

class Menu
{
    use ConstantsOptionTrait;

    // 状态 (1正常 2停用)
    public const STATUS_NORMAL  = 1;
    public const STATUS_DISABLE = 2;
    public static array $status_list = [
        self::STATUS_NORMAL  => 'menu.enums.status.1',
        self::STATUS_DISABLE => 'menu.enums.status.2',
    ];
}
