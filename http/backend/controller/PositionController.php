<?php

namespace http\backend\controller;

use app\controller\BasicController;
use app\exception\UnprocessableEntityException;
use app\lib\annotation\OperationLog;
use app\lib\annotation\Permission;
use app\lib\enum\ResultCode;
use app\model\enums\PolicyType;
use app\router\Annotations\DeleteMapping;
use app\router\Annotations\GetMapping;
use app\router\Annotations\PostMapping;
use app\router\Annotations\PutMapping;
use app\router\Annotations\RestController;
use app\service\DepartmentService;
use app\service\PositionService;
use DI\Attribute\Inject;
use Illuminate\Validation\Rule;
use support\Request;
use support\Response;

#[RestController("/admin")]
class PositionController extends BasicController
{
    #[Inject]
    protected PositionService $service;

    #[Inject]
    protected DepartmentService $departmentService;

    #[GetMapping('/position/list')]
    #[Permission(code: 'permission:position:index')]
    #[OperationLog('岗位列表')]
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

    // batchDataPermission
    #[PutMapping('/position/{id}/data_permission')]
    #[Permission(code: 'permission:position:data_permission')]
    #[OperationLog('设置岗位数据权限')]
    public function dataPermissionListForPosition(Request $request, int $id): Response
    {
        $validator = validate($request->all(), [
            'policy_type' => [
                'required',
                'string',
                Rule::enum(PolicyType::class),
            ],
            'value'       => [
                'sometimes',
                'array',
                'min:1',
            ],
        ]);

        if ($validator->fails()) {
            throw new UnprocessableEntityException(ResultCode::UNPROCESSABLE_ENTITY, $validator->errors()->first());
        }
        $validatedData = $validator->validate();
        $this->service->batchDataPermission($id, $validatedData);
        return $this->success();
    }

    // create
    #[PostMapping('/position')]
    #[Permission(code: 'permission:position:save')]
    #[OperationLog('创建岗位')]
    public function create(Request $request): Response
    {
        $validator = validate($request->all(), [
            'name'    => [
                'required',
                'string',
                'max:60',
                function ($attribute, $value, $fail) {
                    if ($this->service->repository->getQuery()->where($attribute, $value)->exists()) {
                        $fail(trans('unique', [':attribute' => $attribute], 'validation'));
                    }
                }

            ],
            'dept_id' => [
                'required',
                'integer',
                'min:1',
                function ($attribute, $value, $fail) {
                    if (!$this->departmentService->repository->findById($value)) {
                        $fail(trans('exists', [':attribute' => $attribute], 'validation'));
                    }
                }
            ],
        ]);
        if ($validator->fails()) {
            throw new UnprocessableEntityException(ResultCode::UNPROCESSABLE_ENTITY, $validator->errors()->first());
        }
        // $validatedData = $validator->validate();
        $this->service->create(array_merge(
            $request->all(),
            [
                'created_by' => $request->user->id,
            ]
        ));
        return $this->success();
    }

    // save
    #[PutMapping('/position/{id}')]
    #[Permission(code: 'permission:position:update')]
    #[OperationLog('编辑岗位')]
    public function save(Request $request, int $id): Response
    {
        $validator = validate($request->all(), [
            'name' => [
                'required',
                'string',
                'max:60',
                //'unique:position,name',
                function ($attribute, $value, $fail) use ($id) {
                    if ($this->service->repository->getQuery()->where($attribute, $value)->where('id', '<>', $id)->exists()) {
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
    #[DeleteMapping('position')]
    #[Permission(code: 'permission:position:delete')]
    #[OperationLog('删除岗位')]
    public function delete(Request $request): Response
    {
        $this->service->deleteById($request->all());
        return $this->success();
    }

}
