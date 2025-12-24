<?php

namespace http\tenant\controller;

use app\constants\TenantUser;
use app\controller\BasicController;
use app\exception\UnprocessableEntityException;
use app\lib\enum\ResultCode;
use app\router\Annotations\DeleteMapping;
use app\router\Annotations\GetMapping;
use app\router\Annotations\PostMapping;
use app\router\Annotations\PutMapping;
use app\router\Annotations\RestController;
use app\service\TenantUserService;
use DI\Attribute\Inject;
use support\Request;
use support\Response;

#[RestController("/tenant/tenant")]
class TenantUserController extends BasicController
{
    #[Inject]
    protected TenantUserService $service;

    #[GetMapping('/tenant_user/list')]
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
    #[DeleteMapping('/tenant_user/real_delete')]
    public function realDelete(Request $request): Response
    {
        return $this->service->realDelete((array)$request->all()) ? $this->success() : $this->error();
    }

    // 单个或批量恢复在回收站的数据
    #[PutMapping('/tenant_user/recovery')]
    public function recovery(Request $request): Response
    {
        return $this->service->recovery((array)$request->input('ids', [])) ? $this->success() : $this->error();
    }

    #[PostMapping('/tenant_user')]
    public function create(Request $request): Response
    {
        $validator = validate($request->all(), [
            'tenant_id'         => 'required|string|max:20',
            'username'          => 'required|string|max:50',
            'phone'             => 'required|string|max:20',
            'status'            => ['required', 'boolean'],
            'is_enabled_google' => ['required', 'boolean'],
            'avatar'            => 'string|max:255',
            'remark'            => 'string|max:255',
            'ip_whitelist'      => 'present',
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

    #[PutMapping('/tenant_user/password')]
    public function password(Request $request): Response
    {
        $validator = validate($request->all(), [
            'id' => 'required|integer|between:1,4294967295',
        ]);
        if ($validator->fails()) {
            throw new UnprocessableEntityException(ResultCode::UNPROCESSABLE_ENTITY, $validator->errors()->first());
        }
        $validatedData = $validator->validate();
        return $this->service->resetPassword($validatedData['id'])
            ? $this->success()
            : $this->error();
    }
    // 重置google密钥
    #[PutMapping('/tenant_user/resetGoogle2FaSecret/{id}')]
    public function resetGoogle2FaSecret(Request $request, int $id): Response
    {
        return $this->service->repository->getQuery()->where('id', $id)->update([
            'is_enabled_google' => TenantUser::GOOGLE_STATUS_DISABLE,
            'is_bind_google'    => TenantUser::GOOGLE_BIND_NO,
            'google_secret'     => '',
        ]) > 0 ? $this->success() : $this->error();
    }
    #[PutMapping('/tenant_user/{id}')]
    public function update(Request $request, int $id): Response
    {
        $validator = validate($request->all(), [
            'tenant_id'         => 'required|string|max:20',
            'username'          => 'required|string|max:50',
            'phone'             => 'required|string|max:20',
            'status'            => ['required', 'boolean'],
            'is_enabled_google' => ['required', 'boolean'],
            'avatar'            => 'string|max:255',
            'remark'            => 'string|max:255',
            'ip_whitelist'      => 'present',
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

    #[DeleteMapping('/tenant_user')]
    public function delete(Request $request): Response
    {
        $this->service->deleteById($request->all());
        return $this->success();
    }

}
