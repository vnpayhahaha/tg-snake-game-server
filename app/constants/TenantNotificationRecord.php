<?php

namespace app\constants;

use app\constants\lib\ConstantsOptionTrait;

class TenantNotificationRecord
{
    use ConstantsOptionTrait;

    //   `status` tinyint(2) NOT NULL DEFAULT '0' COMMENT '回调状态:0-失败 1-成功 ',
    public const STATUS_FAIL = 0;
    public const STATUS_SUCCESS = 1;
}