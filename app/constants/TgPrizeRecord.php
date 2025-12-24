<?php

namespace app\constants;

use app\constants\lib\ConstantsOptionTrait;

class TgPrizeRecord
{
    use ConstantsOptionTrait;

    // 状态: 1-待处理 2-转账中 3-已完成 4-失败 5-部分失败
    public const STATUS_PENDING = 1;
    public const STATUS_TRANSFERRING = 2;
    public const STATUS_COMPLETED = 3;
    public const STATUS_FAILED = 4;
    public const STATUS_PARTIAL_FAILED = 5;
    public static array $status_list = [
        self::STATUS_PENDING         => 'tg_prize_record.enums.status.1',
        self::STATUS_TRANSFERRING    => 'tg_prize_record.enums.status.2',
        self::STATUS_COMPLETED       => 'tg_prize_record.enums.status.3',
        self::STATUS_FAILED          => 'tg_prize_record.enums.status.4',
        self::STATUS_PARTIAL_FAILED  => 'tg_prize_record.enums.status.5',
    ];

    // 中奖流水号前缀
    public const PRIZE_SERIAL_PREFIX = 'WIN';

    // 流水号分隔符
    public const SERIAL_SEPARATOR = '-';

    // 中奖类型（用于业务扩展）
    public const PRIZE_TYPE_JACKPOT = 1;  // 连号大奖（清空奖池）
    public const PRIZE_TYPE_RANGE = 2;    // 区间匹配
}
