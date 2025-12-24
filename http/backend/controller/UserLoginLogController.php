<?php

namespace http\backend\controller;

use app\controller\BasicController;
use app\lib\annotation\Permission;
use app\router\Annotations\DeleteMapping;
use app\router\Annotations\GetMapping;
use app\router\Annotations\RestController;
use app\service\UserLoginLogService;
use DI\Attribute\Inject;
use support\Request;
use support\Response;

#[RestController("/admin")]
class UserLoginLogController extends BasicController
{
    #[Inject]
    protected UserLoginLogService $service;

    /**
     * 用户登录日志列表
     * @param Request $request
     * @return Response
     */
    #[GetMapping('/user-login-log/list')]
    #[Permission(code: 'log:userLogin:list')]
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
     * 删除用户登录日志
     * @param Request $request
     * @return Response
     */
    #[DeleteMapping('/user-login-log')]
    #[Permission(code: 'log:userLogin:delete')]
    public function delete(Request $request): Response
    {
        $this->service->deleteById($request->input('ids'));
        return $this->success();
    }

}
