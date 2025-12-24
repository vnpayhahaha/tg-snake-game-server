<?php

namespace app\constants;

use app\constants\lib\ConstantsOptionTrait;

class TenantNotificationQueue
{
    use ConstantsOptionTrait;

    // int $notification_type 通知类型:1-系统通知 2-订单通知 3-账单通知
    public const NOTIFICATION_TYPE_SYSTEM = 1;
    public const NOTIFICATION_TYPE_ORDER = 2;
    public const NOTIFICATION_TYPE_BILL = 3;
    public static array $notification_type_list = [
        self::NOTIFICATION_TYPE_SYSTEM => 'tenant_notification_queue.enums.notification_type.system',
        self::NOTIFICATION_TYPE_ORDER  => 'tenant_notification_queue.enums.notification_type.order',
        self::NOTIFICATION_TYPE_BILL   => 'tenant_notification_queue.enums.notification_type.bill',
    ];

    //  int $execute_status 执行状态:0-待执行 1-执行中 2-成功 3-失败
    public const EXECUTE_STATUS_WAITING = 0;
    public const EXECUTE_STATUS_EXECUTING = 1;
    public const EXECUTE_STATUS_SUCCESS = 2;
    public const EXECUTE_STATUS_FAILURE = 3;
    public static array $execute_status_list = [
        self::EXECUTE_STATUS_WAITING   => 'tenant_notification_queue.enums.execute_status.waiting',
        self::EXECUTE_STATUS_EXECUTING => 'tenant_notification_queue.enums.execute_status.executing',
        self::EXECUTE_STATUS_SUCCESS   => 'tenant_notification_queue.enums.execute_status.success',
        self::EXECUTE_STATUS_FAILURE   => 'tenant_notification_queue.enums.execute_status.failure',
    ];

     // TENANT_NOTIFICATION_QUEUE_NAME
    public const TENANT_NOTIFICATION_QUEUE_NAME = 'tenant-notification-queue-consumer';
}