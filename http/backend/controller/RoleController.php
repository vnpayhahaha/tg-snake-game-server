<?php

namespace http\backend\controller;

use app\controller\BasicController;
use app\exception\UnprocessableEntityException;
use app\lib\annotation\OperationLog;
use app\lib\annotation\Permission;
use app\lib\enum\ResultCode;
use app\model\ModelMenu;
use app\router\Annotations\DeleteMapping;
use app\router\Annotations\GetMapping;
use app\router\Annotations\PostMapping;
use app\router\Annotations\PutMapping;
use app\router\Annotations\RestController;
use app\service\RoleService;
use DI\Attribute\Inject;
use Illuminate\Support\Arr;
use support\Request;
use support\Response;

#[RestController("/admin")]
class RoleController extends BasicController
{

    #[Inject]
    protected RoleService $service;


    #[GetMapping('/role/list')]
    #[Permission(code: 'permission:role:index')]
    #[OperationLog('角色列表')]
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
    #[PostMapping('/role')]
    #[Permission(code: 'permission:role:save')]
    #[OperationLog('创建角色')]
    public function create(Request $request): Response
    {
        $validator = validate($request->all(), [
            'name'   => 'required|string|max:60',
            'code'   => [
                'required',
                'string',
                'max:60',
                'regex:/^[a-zA-Z0-9_]+$/',
                function ($attribute, $value, $fail) {
                    if ($this->service->repository->getModel()->where($attribute, $value)->exists()) {
                        $fail(trans('unique', [':attribute' => $attribute], 'validation'));
                    }
                }
            ],
            'status' => 'sometimes|integer|in:1,2',
            'sort'   => 'required|integer',
            'remark' => 'nullable|string|max:255',
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

    // save
    #[PutMapping('/role/{id}')]
    #[Permission(code: 'permission:role:update')]
    #[OperationLog('编辑角色')]
    public function save(Request $request, int $id): Response
    {
        $validator = validate($request->all(), [
            'name' => 'required|string|max:60',
            'code' => [
                'required',
                'string',
                'max:60',
                'regex:/^[a-zA-Z0-9_]+$/',
                function ($attribute, $value, $fail) use ($id) {
                    if ($this->service->repository->getModel()->where($attribute, $value)->where('id', '<>', $id)->exists()) {
                        $fail(trans('unique', [':attribute' => $attribute], 'validation'));
                    }
                }
            ]
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
    #[DeleteMapping('/role')]
    #[Permission(code: 'permission:role:delete')]
    #[OperationLog('删除角色')]
    public function delete(Request $request): Response
    {
        $this->service->deleteById($request->all());
        return $this->success();
    }

    // 获取角色权限列表
    #[GetMapping('/role/{id}/permissions')]
    #[Permission(code: 'permission:role:getMenu')]
    #[OperationLog('获取角色权限列表')]
    public function permissionListForRole(int $id): Response
    {
        return $this->success($this->service->getRolePermission($id)->map(static fn(ModelMenu $menu) => $menu->only([
            'id', 'name',
        ]))->toArray());
    }

    // 赋予角色权限
    #[PutMapping('/role/{id}/permissions')]
    #[Permission(code: 'permission:role:setMenu')]
    #[OperationLog('赋予角色权限')]
    public function batchGrantPermissionsForRole(Request $request, int $id): Response
    {
        if (!$this->service->existsById($id)) {
            throw new UnprocessableEntityException(code: ResultCode::ROLE_NOT_EXIST);
        }
        $validator = validate($request->all(), [
            'permissions'   => 'sometimes|array',
            'permissions.*' => [
                'string',
                'regex:/^[a-zA-Z_:]+$/',
            ],
        ]);
        if ($validator->fails()) {
            throw new UnprocessableEntityException(ResultCode::UNPROCESSABLE_ENTITY, $validator->errors()->first());
        }
        $validatedData = $validator->validate();
        $permissionsCode = Arr::get($validatedData, 'permissions', []);
        $this->service->batchGrantPermissionsForRole($id, $permissionsCode);
        return $this->success();
    }
}
