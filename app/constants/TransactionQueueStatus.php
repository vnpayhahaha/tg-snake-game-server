<?php

namespace app\constants;

use app\constants\lib\ConstantsOptionTrait;

class TransactionQueueStatus
{
    use ConstantsOptionTrait;

    // 状态:0-处理中 1-成功 2-失败 3-挂起(定时任务扫描长时间没处理，队列丢失)
    const STATUS_PROCESSING = 0;
    const STATUS_SUCCESS    = 1;
    const STATUS_FAIL       = 2;
    const STATUS_SUSPEND    = 3;
    public static array $status_list = [
        self::STATUS_PROCESSING => 'transaction_queue_status.enums.status.processing',
        self::STATUS_SUCCESS    => 'transaction_queue_status.enums.status.success',
        self::STATUS_FAIL       => 'transaction_queue_status.enums.status.fail',
        self::STATUS_SUSPEND    => 'transaction_queue_status.enums.status.suspend',
    ];
}
