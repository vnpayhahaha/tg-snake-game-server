<?php

namespace app\constants;

use app\constants\lib\ConstantsOptionTrait;

class TgSnakeNode
{
    use ConstantsOptionTrait;

    // 节点状态: 1-活跃 2-已中奖 3-已派奖 4-已归档
    public const STATUS_ACTIVE = 1;
    public const STATUS_MATCHED = 2;
    public const STATUS_TRANSFERRED = 3;
    public const STATUS_ARCHIVED = 4;
    public static array $status_list = [
        self::STATUS_ACTIVE      => 'tg_snake_node.enums.status.1',
        self::STATUS_MATCHED     => 'tg_snake_node.enums.status.2',
        self::STATUS_TRANSFERRED => 'tg_snake_node.enums.status.3',
        self::STATUS_ARCHIVED    => 'tg_snake_node.enums.status.4',
    ];

    // 凭证号前缀
    public const TICKET_PREFIX = 'TKT';

    // 流水号分隔符
    public const SERIAL_SEPARATOR = '-';
}
