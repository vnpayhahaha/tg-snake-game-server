<?php

namespace http\backend\controller;

use app\controller\BasicController;
use app\lib\annotation\OperationLog;
use app\lib\annotation\Permission;
use app\router\Annotations\GetMapping;
use app\router\Annotations\RestController;
use app\service\TenantNotificationRecordService;
use DI\Attribute\Inject;
use support\Request;
use support\Response;

#[RestController("/admin/tenant")]
class TenantNotificationRecordController extends BasicController
{
    #[Inject]
    protected TenantNotificationRecordService $service;

    #[GetMapping('/tenant_notification_record/list')]
    #[Permission(code: 'tenant:tenant_notification_record:list')]
    #[OperationLog('租户通知记录列表')]
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