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
use app\service\DepartmentService;
use DI\Attribute\Inject;
use support\Request;
use support\Response;

#[RestController("/admin")]
class DepartmentController extends BasicController
{

    #[Inject]
    protected DepartmentService $service;

    #[GetMapping('/department/list')]
    #[Permission(code: 'permission:department:index')]
    #[OperationLog('部门列表')]
    public function pageList(Request $request): Response
    {
        return $this->success(
            data: [
                'list' => $this->service->getList($this->getRequestData()),
            ]);
    }

    // create
    #[PostMapping('/department')]
    #[Permission(code: 'permission:department:save')]
    #[OperationLog('创建部门')]
    public function create(Request $request): Response
    {
        $validator = validate($request->post(), [
            'name'      => [
                'required',
                'string',
                'max:60',
                //  'unique:department,name',
                function ($attribute, $value, $fail) {
                    if ($this->service->repository->getModel()->where('name', $value)->exists()) {
                        $fail(trans('unique', [':attribute' => $attribute], 'validation'));
                    }
                },
            ],
            'parent_id' => 'sometimes|integer',
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
    #[PutMapping('/department/{id}')]
    #[Permission(code: 'permission:department:update')]
    #[OperationLog('保存部门')]
    public function save(Request $request, int $id): Response
    {
        $validator = validate($request->post(), [
            'name'      => [
                'required',
                'string',
                'max:60',
                //  'unique:department,name',
                function ($attribute, $value, $fail) use ($id) {
                    if ($this->service->repository->getModel()->where($attribute, $value)->where('id', '<>', $id)->exists()) {
                        $fail(trans('unique', [':attribute' => $attribute], 'validation'));
                    }
                }
            ],
            'parent_id' => 'sometimes|integer',
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
    #[DeleteMapping('/department')]
    #[Permission(code: 'permission:department:delete')]
    #[OperationLog('删除部门')]
    public function delete(Request $request): Response
    {
        $this->service->deleteById($request->all());
        return $this->success();
    }
}
