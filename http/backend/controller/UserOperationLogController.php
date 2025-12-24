<?php

namespace http\backend\controller;

use app\controller\BasicController;
use app\lib\annotation\Permission;
use app\router\Annotations\DeleteMapping;
use app\router\Annotations\GetMapping;
use app\router\Annotations\RestController;
use app\service\UserOperationLogService;
use DI\Attribute\Inject;
use support\Request;
use support\Response;

#[RestController("/admin")]
class UserOperationLogController extends BasicController
{
    #[Inject]
    protected UserOperationLogService $service;

    /**
     * 用户操作日志列表
     * @param Request $request
     * @return Response
     */
    #[GetMapping('/user-operation-log/list')]
    #[Permission(code: 'log:userOperation:list')]
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

    /**
     * 删除用户操作日志
     * @param Request $request
     * @return Response
     */
    #[DeleteMapping('/user-operation-log')]
    #[Permission(code: 'log:userOperation:delete')]
    public function delete(Request $request): Response
    {
        $this->service->deleteById($request->input('ids'));
        return $this->success();
    }

}
