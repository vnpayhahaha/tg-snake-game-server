<?php

namespace app\constants;

use app\constants\lib\ConstantsOptionTrait;

class ChannelCallbackRecord
{
    use ConstantsOptionTrait;

    // 状态: 0-接收中, 1-接收成功, 2-接收失败
    public const STATUS_RECEIVING = 0;
    public const STATUS_SUCCESS = 1;
    public const STATUS_FAIL = 2;
    public static array $status_list = [
        self::STATUS_RECEIVING => 'channel_callback_record.enums.status.0',
        self::STATUS_SUCCESS   => 'channel_callback_record.enums.status.1',
        self::STATUS_FAIL      => 'channel_callback_record.enums.status.2',
    ];


}