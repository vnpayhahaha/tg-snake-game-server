<?php

namespace http\backend\controller;

use app\controller\BasicController;
use app\exception\UnprocessableEntityException;
use app\lib\annotation\OperationLog;
use app\lib\annotation\Permission;
use app\lib\enum\ResultCode;
use app\router\Annotations\DeleteMapping;
use app\router\Annotations\GetMapping;
use app\router\Annotations\PostMapping;
use app\router\Annotations\RestController;
use app\service\LeaderService;
use DI\Attribute\Inject;
use support\Request;
use support\Response;

#[RestController("/admin")]
class LeaderController extends BasicController
{

    #[Inject]
    protected LeaderService $service;

    #[GetMapping('/leader/list')]
    #[Permission(code: 'permission:leader:index')]
    #[OperationLog('获取部门领导列表')]
    public function pageList(Request $request): Response
    {
        return $this->success(data: $this->service->page(
            $request->all(),
            $this->getCurrentPage(),
            $this->getPageSize()
        ));
    }

    // create
    #[PostMapping('/leader')]
    #[Permission(code: 'permission:leader:save')]
    #[OperationLog('创建部门领导')]
    public function create(Request $request): Response
    {
        $validator = validate($request->all(), [
            'user_id' => 'required|array',
            'dept_id' => 'required|integer',
        ]);
        if ($validator->fails()) {
            throw new UnprocessableEntityException(ResultCode::UNPROCESSABLE_ENTITY, $validator->errors()->first());
        }
        $validatedData = $validator->validate();
        $this->service->create(array_merge(
            $validatedData,
            [
                'created_by' => $request->user->id,
            ]
        ));
        return $this->success();
    }

    #[DeleteMapping('/leader')]
    #[Permission(code: 'permission:leader:delete')]
    #[OperationLog('删除部门领导')]
    public function delete(Request $request): Response
    {
        $this->service->deleteByDoubleKey($request->all());
        return $this->success();
    }
}
