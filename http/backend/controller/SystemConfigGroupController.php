<?php

namespace http\backend\controller;

use app\controller\BasicController;
use app\lib\annotation\OperationLog;
use app\lib\annotation\Permission;
use app\router\Annotations\DeleteMapping;
use app\router\Annotations\GetMapping;
use app\router\Annotations\PostMapping;
use app\router\Annotations\PutMapping;
use app\router\Annotations\RestController;
use app\service\SystemConfigGroupService;
use DI\Attribute\Inject;
use support\Request;
use support\Response;

#[RestController("/admin")]
class SystemConfigGroupController extends BasicController
{
    #[Inject]
    protected SystemConfigGroupService $service;

    #[GetMapping('/setting/configGroup/list')]
    #[Permission(code: 'system:config:group:list')]
    #[OperationLog('系统配置分组列表')]
    public function pageList(Request $request): Response
    {
        return $this->success(data: $this->service->getList([]));
    }

    #[PostMapping('/setting/configGroup')]
    #[Permission(code: 'system:config:group:create')]
    #[OperationLog('创建系统配置分组')]
    public function create(Request $request): Response
    {
        $this->service->create(array_merge(
            $request->all(),
            [
                'created_by' => $request->user->id,
            ]
        ));
        return $this->success();
    }

    #[PutMapping('/setting/configGroup/{id}')]
    #[Permission(code: 'system:config:group:update')]
    #[OperationLog('更新系统配置分组')]
    public function update(Request $request, int $id): Response
    {
        $this->service->updateById($id, array_merge(
            $request->all(),
            [
                'updated_by' => $request->user->id,
            ]
        ));
        return $this->success();
    }

    #[DeleteMapping('/setting/configGroup')]
    #[Permission(code: 'system:config:group:delete')]
    #[OperationLog('删除系统配置分组')]
    public function delete(Request $request): Response
    {
        $this->service->deleteById($request->all());
        return $this->success();
    }

}
