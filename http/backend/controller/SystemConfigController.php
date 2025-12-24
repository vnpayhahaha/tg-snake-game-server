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
use app\service\SystemConfigService;
use DI\Attribute\Inject;
use support\Request;
use support\Response;

#[RestController("/admin")]
class SystemConfigController extends BasicController
{
    #[Inject]
    protected SystemConfigService $service;

    #[GetMapping('/setting/config/list')]
    #[Permission(code: 'system:config:list')]
    #[OperationLog('系统配置列表')]
    public function pageList(Request $request): Response
    {
        return $this->success(
            $this->service->page(
                $request->all(),
                $this->getCurrentPage(),
                $this->getPageSize()
            )
        );
    }

    // details
    #[GetMapping('/setting/config/details/{id}')]
    #[Permission(code: 'system:config:details')]
    #[OperationLog('获取系统配置详情')]
    public function details(int $id): Response
    {
        return $this->success($this->service->getDetails([
            'group_id' => $id,
        ]));
    }

    #[PostMapping('/setting/config')]
    #[Permission(code: 'system:config:create')]
    #[OperationLog('创建系统配置')]
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

    #[PutMapping('/setting/config/{id}')]
    #[Permission(code: 'system:config:update')]
    #[OperationLog('更新系统配置')]
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

    #[DeleteMapping('/setting/config')]
    #[Permission(code: 'system:config:delete')]
    #[OperationLog('删除系统配置')]
    public function delete(Request $request): Response
    {
        $this->service->deleteByKey($request->all());
        return $this->success();
    }

    // 系统配置批量更新
    #[PostMapping('/setting/config/batchUpdate')]
    #[Permission(code: 'system:config:batchUpdate')]
    #[OperationLog('批量更新系统配置')]
    public function batchUpdate(Request $request): Response
    {
        $this->service->upsertData($request->all());
        return $this->success();
    }

}
