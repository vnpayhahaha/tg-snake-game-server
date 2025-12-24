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
use app\router\Annotations\PutMapping;
use app\router\Annotations\RestController;
use app\service\TenantAppService;
use DI\Attribute\Inject;
use support\Request;
use support\Response;

#[RestController("/admin/tenant")]
class TenantAppController extends BasicController
{
    #[Inject]
    protected TenantAppService $service;

    #[GetMapping('/tenant_app/list')]
    #[Permission(code: 'tenant:tenantApp:list')]
    #[OperationLog('租户应用管理列表')]
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

    // 单个或批量真实删除数据 （清空回收站）
    #[DeleteMapping('/tenant_app/real_delete')]
    #[Permission(code: 'tenant:tenantApp:realDelete')]
    #[OperationLog('清空回收站')]
    public function realDelete(Request $request): Response
    {
        return $this->service->realDelete((array)$request->all()) ? $this->success() : $this->error();
    }

    // 单个或批量恢复在回收站的数据
    #[PutMapping('/tenant_app/recovery')]
    #[Permission(code: 'tenant:tenantApp:recovery')]
    #[OperationLog('租户回收站恢复')]
    public function recovery(Request $request): Response
    {
        return $this->service->recovery((array)$request->input('ids', [])) ? $this->success() : $this->error();
    }

    #[PostMapping('/tenant_app')]
    #[Permission(code: 'tenant:tenantApp:create')]
    #[OperationLog('创建租户应用')]
    public function create(Request $request): Response
    {
        $validator = validate($request->all(), [
            'app_name'    => 'required|string|max:32',
            'app_key'     => 'required|string|max:16',
            'app_secret'  => 'required|string|max:128',
            'remark'      => 'string|max:255',
            'tenant_id'   => 'required|string|max:20',
            'status'      => ['required', 'boolean'],
            'description' => 'string|max:255'
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

    #[PutMapping('/tenant_app/{id}')]
    #[Permission(code: 'tenant:tenantApp:update')]
    #[OperationLog('编辑租户应用')]
    public function update(Request $request, int $id): Response
    {
        $validator = validate($request->all(), [
            'app_name'    => 'required|string|max:32',
            'app_key'     => 'required|string|max:16',
            'app_secret'  => 'required|string|max:128',
            'remark'      => 'string|max:255',
            'tenant_id'   => 'required|string|max:20',
            'status'      => ['required', 'boolean'],
            'description' => 'string|max:255'
        ]);
        if ($validator->fails()) {
            return $this->error(ResultCode::UNPROCESSABLE_ENTITY, $validator->errors()->first());
        }
        $validatedData = $validator->validate();
        $this->service->updateById($id, array_merge(
            $validatedData,
            [
                'updated_by' => $request->user->id,
            ]
        ));
        return $this->success();
    }

    // 删除
    #[DeleteMapping('/tenant_app')]
    #[Permission(code: 'tenant:tenantApp:delete')]
    #[OperationLog('删除租户应用')]
    public function delete(Request $request): Response
    {
        $this->service->deleteById($request->all());
        return $this->success();
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

    #[GetMapping('/tenant_app/get_app_key')]
    public function getAppKey(): Response
    {
        return $this->success(
            ['app_key' => $this->service->generateAppKey()]
        );
    }

    #[GetMapping('/tenant_app/get_app_secret')]
    public function getAppSecret(): Response
    {
        return $this->success(
            ['app_secret' => $this->service->generateAppSecret()]
        );
    }
}
