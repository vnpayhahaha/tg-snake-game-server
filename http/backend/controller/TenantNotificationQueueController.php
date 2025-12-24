<?php

namespace http\backend\controller;

use app\controller\BasicController;
use app\lib\annotation\OperationLog;
use app\lib\annotation\Permission;
use app\router\Annotations\GetMapping;
use app\router\Annotations\RestController;
use app\service\TenantNotificationQueueService;
use DI\Attribute\Inject;
use support\Request;
use support\Response;

#[RestController("/admin/tenant")]
class TenantNotificationQueueController extends BasicController
{
    #[Inject]
    protected TenantNotificationQueueService $service;

    #[GetMapping('/tenant_notification_queue/list')]
    #[Permission(code: 'tenant:tenant_notification_queue:list')]
    #[OperationLog('租户通知列表')]
    public function pageList(Request $request): Response
    {
        return $this->success(
            data: $this->service->page(
                $request->all(),
                $this->getCurrentPage(),
                $this->getPageSize(),
            )
        );
    }

}
