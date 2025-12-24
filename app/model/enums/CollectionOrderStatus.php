<?php

namespace app\model\enums;

use app\constants\CollectionOrder;
use app\lib\traits\ConstantsTrait;

class CollectionOrderStatus
{
    use ConstantsTrait;
    //  0-创建 10-处理中 20-成功 30-挂起 40-失败 41-已取消 43-已失效 44-已退款
    public const STATUS_CREATE = CollectionOrder::STATUS_CREATE;
    public const STATUS_PROCESSING = CollectionOrder::STATUS_PROCESSING;
    public const STATUS_SUCCESS = CollectionOrder::STATUS_SUCCESS;
    public const STATUS_SUSPEND = CollectionOrder::STATUS_SUSPEND;
    public const STATUS_FAIL = CollectionOrder::STATUS_FAIL;
    public const STATUS_CANCEL = CollectionOrder::STATUS_CANCEL;
    public const STATUS_INVALID = CollectionOrder::STATUS_INVALID;
    public const STATUS_REFUND = CollectionOrder::STATUS_REFUND;

}