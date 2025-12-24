<?php

namespace app\constants;

use app\constants\lib\ConstantsOptionTrait;

class TgPrizeTransfer
{
    use ConstantsOptionTrait;

    // 状态: 1-待处理 2-处理中 3-成功 4-失败
    public const STATUS_PENDING = 1;
    public const STATUS_PROCESSING = 2;
    public const STATUS_SUCCESS = 3;
    public const STATUS_FAILED = 4;
    public static array $status_list = [
        self::STATUS_PENDING    => 'tg_prize_transfer.enums.status.1',
        self::STATUS_PROCESSING => 'tg_prize_transfer.enums.status.2',
        self::STATUS_SUCCESS    => 'tg_prize_transfer.enums.status.3',
        self::STATUS_FAILED     => 'tg_prize_transfer.enums.status.4',
    ];

    // 最大重试次数
    public const MAX_RETRY_COUNT = 3;

    // 重试间隔（秒）
    public const RETRY_DELAY_SECONDS = 60;
}
