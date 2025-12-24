<?php

namespace http\tenant\controller;

use app\controller\BasicController;
use app\router\Annotations\GetMapping;
use app\router\Annotations\RestController;
use app\service\TenantAppService;
use DI\Attribute\Inject;
use support\Request;
use support\Response;

#[RestController("/tenant/tenant")]
class TenantAppController extends BasicController
{
    #[Inject]
    protected TenantAppService $service;

    #[GetMapping('/tenant_app/list')]
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

    #[GetMapping('/tenant_app_dict/remote')]
    public function remote(Request $request): Response
    {
        $fields = [
            'id',
            'tenant_id',
            'app_name',
            'status',
        ];
        return $this->success(
            $this->service->getList([])->map(static fn($model) => $model->only($fields))
        );
    }


}
