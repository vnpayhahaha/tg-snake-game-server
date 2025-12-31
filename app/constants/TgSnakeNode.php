<?php

namespace app\constants;

use app\constants\lib\ConstantsOptionTrait;

class TgSnakeNode
{
    use ConstantsOptionTrait;

    // 节点状态: 1-活跃 2-已中奖 3-未中奖
    public const STATUS_ACTIVE = 1;
    public const STATUS_MATCHED = 2;
    public const STATUS_CANCELLED = 3;
    public static array $status_list = [
        self::STATUS_ACTIVE    => 'tg_snake_node.enums.status.1',
        self::STATUS_MATCHED   => 'tg_snake_node.enums.status.2',
        self::STATUS_CANCELLED => 'tg_snake_node.enums.status.3',
    ];

    // 凭证号前缀
    public const TICKET_PREFIX = 'TKT';

    // 流水号分隔符
    public const SERIAL_SEPARATOR = '-';
}
