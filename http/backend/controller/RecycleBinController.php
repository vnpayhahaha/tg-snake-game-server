<?php

namespace http\backend\controller;

use app\controller\BasicController;
use app\lib\annotation\OperationLog;
use app\lib\annotation\Permission;
use app\router\Annotations\GetMapping;
use app\router\Annotations\PutMapping;
use app\router\Annotations\RestController;
use app\service\RecycleBinService;
use DI\Attribute\Inject;
use support\Request;
use support\Response;

#[RestController("/admin")]
class RecycleBinController extends BasicController
{
    #[Inject]
    protected RecycleBinService $service;


    #[GetMapping('/recycle_bin/list')]
    #[Permission(code: 'recycle_bin:index')]
    #[OperationLog('回收站列表')]
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

    //恢复数据
    #[PutMapping('/recycle_bin/{id}/restore')]
    #[Permission(code: 'recycle_bin:update')]
    #[OperationLog('恢复数据')]
    public function restore(Request $request, int $id): Response
    {
        $this->service->restoreRecycleBin($id);
        return $this->success();
    }

}
