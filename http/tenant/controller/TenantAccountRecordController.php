<?php

namespace http\tenant\controller;

use app\controller\BasicController;
use app\router\Annotations\GetMapping;
use app\router\Annotations\RestController;
use app\service\TenantAccountRecordService;
use DI\Attribute\Inject;
use support\Request;
use support\Response;

#[RestController("/tenant")]
class TenantAccountRecordController  extends BasicController
{
    #[Inject]
    protected TenantAccountRecordService $service;

    #[GetMapping('/tenant_account_record/list')]
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
