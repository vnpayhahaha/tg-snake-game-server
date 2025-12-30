<?php

namespace app\constants;

use app\constants\lib\ConstantsOptionTrait;

class TgGameGroupConfigLog
{
    use ConstantsOptionTrait;

    // change_source 变更来源:1=后台编辑,2=TG群指令
    public const  CHANGE_SOURCE_ADMIN = 1;
    public const  CHANGE_SOURCE_TG = 2;

    public static array $change_source_list = [
        self::CHANGE_SOURCE_ADMIN => '后台编辑',
        self::CHANGE_SOURCE_TG    => 'TG群指令',
    ];
}