<?php

namespace app\event;

use app\event\Dto\OperationEventDto;
use app\service\UserOperationLogService;
use DI\Attribute\Inject;

class OperationEvent
{
    #[Inject]
    protected UserOperationLogService $service;

    /**
     * 处理事件
     * @param OperationEventDto $eventObj
     */
    public function process(OperationEventDto $eventObj): void
    {
        $requestInfo = $eventObj->getRequestInfo();
        $this->service->create($requestInfo);
    }
}
