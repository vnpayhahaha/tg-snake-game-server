<?php

namespace http\backend\controller;

use app\controller\BasicController;
use app\lib\annotation\OperationLog;
use app\lib\annotation\Permission;
use app\router\Annotations\GetMapping;
use app\router\Annotations\RestController;
use app\service\TenantAccountRecordService;
use DI\Attribute\Inject;
use support\Request;
use support\Response;

#[RestController("/admin/tenant")]
class TenantAccountRecordController extends BasicController
{
    #[Inject]
    protected TenantAccountRecordService $service;

    #[GetMapping('/tenant_account_record/list')]
    #[Permission(code: 'tenant:tenant_account_record:list')]
    #[OperationLog('租户账单记录列表')]
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
