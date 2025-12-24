<?php

namespace app\event;

use app\event\Dto\TenantAppLogEventDto;
use app\service\TenantAppLogService;
use support\Container;

class TenantAppLogEvent
{

    /**
     * 处理事件
     * @param TenantAppLogEventDto $eventObj
     */
    public function process(TenantAppLogEventDto $eventObj): void
    {
        $tenantAppLog = Container::make(TenantAppLogService::class);
        $requestInfo = $eventObj->getRequestInfo();
        var_dump('TenantAppLogEvent ==');
        $tenantAppLog->create($requestInfo);
    }
}
