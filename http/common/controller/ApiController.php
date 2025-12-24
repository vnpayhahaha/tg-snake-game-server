<?php

namespace http\common\controller;

use app\controller\BasicController;
use app\lib\enum\ResultCode;
use app\service\TenantApiInterfaceService;
use DI\Attribute\Inject;
use support\Request;
use support\Response;

class ApiController extends BasicController
{
    #[Inject]
    protected TenantApiInterfaceService $service;

    public function api(Request $request, string $api): Response
    {
        $find = $this->service->repository->getQuery()->where('api_name', $api)->first();
        if (!$find) {
            return $this->error(ResultCode::NOT_FOUND, 'api not found');
        }
        return $this->success($find->toArray());
    }
}
