<?php

namespace app\constants;

use app\constants\lib\ConstantsOptionTrait;

class RecycleBin
{
    use ConstantsOptionTrait;

    // $enabled 是否已还原(1已还原 2未还原)
    public const ENABLED_YES = 1;
    public const ENABLED_NO = 2;
    public static array $enabled_list = [
        self::ENABLED_YES => 'recycle_bin.enums.enabled.1',
        self::ENABLED_NO  => 'recycle_bin.enums.enabled.2',
    ];
}
