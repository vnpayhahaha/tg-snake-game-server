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
use app\service\TenantConfigService;
use DI\Attribute\Inject;
use support\Request;
use support\Response;

#[RestController("/admin/tenant")]
class TenantConfigController extends BasicController
{

    #[Inject]
    protected TenantConfigService $service;

    #[GetMapping('/tenant_config/list')]
    #[Permission(code: 'tenant:tenantConfig:list')]
    #[OperationLog('租户配置列表')]
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

    // create
    #[PostMapping('/tenant_config')]
    #[Permission(code: 'tenant:tenantConfig:create')]
    #[OperationLog('添加租户配置')]
    public function create(Request $request): Response
    {
        $validator = validate($request->all(), [
            'tenant_id'  => 'required|string|max:20',
            'group_code' => 'required|string|max:20',
            'code'       => [
                'required',
                'string',
                'max:64',
                function ($attribute, $value, $fail) use ($request) {
                    if ($this->service->repository->getModel()
                        ->where('tenant_id', $request->input('tenant_id'))
                        ->where('code', $value)
                        ->exists()
                    ) {
                        $fail(trans('unique', [':attribute' => $attribute], 'validation'));
                    }
                }
            ],
            'name'       => 'required|string|max:64',
            'content'    => 'string',
            'enabled'    => ['required', 'boolean'],
            'intro'      => 'string|max:250',
            'option'     => 'json|max:1000',
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

    // update
    #[PutMapping('/tenant_config/{id}')]
    #[Permission(code: 'tenant:tenantConfig:update')]
    #[OperationLog('编辑租户配置')]
    public function update(Request $request, int $id): Response
    {
        $validator = validate($request->all(), [
            'tenant_id'  => 'required|string|max:20',
            'group_code' => 'required|string|max:20',
            'code'       => [
                'required',
                'string',
                'max:64',
                function ($attribute, $value, $fail) use ($request, $id) {
                    if ($this->service->repository->getModel()
                        ->where('tenant_id', $request->input('tenant_id'))
                        ->where('code', $value)
                        ->where('id', '<>', $id)
                        ->exists()
                    ) {
                        $fail(trans('unique', [':attribute' => $attribute], 'validation'));
                    }
                }
            ],
            'name'       => 'required|string|max:64',
            'content'    => 'string',
            'enabled'    => ['required', 'boolean'],
            'intro'      => 'string|max:250',
            'option'     => 'json|max:1000',
        ]);
        if ($validator->fails()) {
            throw new UnprocessableEntityException(ResultCode::UNPROCESSABLE_ENTITY, $validator->errors()->first());
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

    // delete
    #[DeleteMapping('/tenant_config')]
    #[Permission(code: 'tenant:tenantConfig:delete')]
    #[OperationLog('删除租户配置')]
    public function delete(Request $request): Response
    {
        $this->service->deleteById($request->all());
        return $this->success();
    }

}
