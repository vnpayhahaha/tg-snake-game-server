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
use app\service\TenantApiInterfaceService;
use DI\Attribute\Inject;
use support\Request;
use support\Response;

#[RestController("/admin/tenant")]
class TenantApiInterfaceController extends BasicController
{
    #[Inject]
    protected TenantApiInterfaceService $service;

    #[GetMapping('/tenant_api_interface/list')]
    #[Permission(code: 'tenant:tenant_api_interface:list')]
    #[OperationLog('租户接口管理列表')]
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

    #[PostMapping('/tenant_api_interface')]
    #[Permission(code: 'tenant:tenant_api_interface:create')]
    #[OperationLog('添加租户接口管理')]
    public function create(Request $request): Response
    {
        $validator = validate($request->all(), [
            'api_name'            => 'required|string|max:100',
            'api_uri'             => 'required|string|max:255',
            'http_method'         => [
                'required',
                'in:GET,POST,PUT,DELETE',
                function ($attribute, $value, $fail) use ($request) {
                    if ($this->service->repository->getModel()
                        ->where('api_uri', $request->input('api_uri'))
                        ->where('http_method', $value)
                        ->exists()
                    ) {
                        $fail(trans('unique', [':attribute' => $attribute], 'validation'));
                    }
                }
            ],
            'request_params'      => 'json',
            'request_params_en'   => 'json',
            'request_example'     => 'json',
            'request_example_en'  => 'json',
            'response_params'     => 'json',
            'response_params_en'  => 'json',
            'response_example'    => 'json',
            'response_example_en' => 'json',
            'description'         => 'string|max:1000',
            'description_en'      => 'string|max:1000',
            'status'              => 'boolean',
            'rate_limit'          => ['integer', 'between:0,1000'],
            'auth_mode'           => ['integer', 'between:0,2'],
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
    #[PutMapping('/tenant_api_interface/{id}')]
    #[Permission(code: 'tenant:tenant_api_interface:update')]
    #[OperationLog('编辑租户接口管理')]
    public function update(Request $request, int $id): Response
    {
        $validator = validate($request->all(), [
            'api_name'            => 'required|string|max:100',
            'api_uri'             => 'required|string|max:255',
            'http_method'         => [
                'required',
                'in:GET,POST,PUT,DELETE',
                function ($attribute, $value, $fail) use ($request, $id) {
                    if ($this->service->repository->getModel()
                        ->where('api_uri', $request->input('api_uri'))
                        ->where('http_method', $value)
                        ->where('id', '<>', $id)
                        ->exists()
                    ) {
                        $fail(trans('unique', [':attribute' => $attribute], 'validation'));
                    }
                }
            ],
            'request_params'      => 'json',
            'request_params_en'   => 'json',
            'request_example'     => 'json',
            'request_example_en'  => 'json',
            'response_params'     => 'json',
            'response_params_en'  => 'json',
            'response_example'    => 'json',
            'response_example_en' => 'json',
            'description'         => 'string|max:1000',
            'description_en'      => 'string|max:1000',
            'status'              => 'boolean',
            'rate_limit'          => ['integer', 'between:0,1000'],
            'auth_mode'           => ['integer', 'between:0,2'],
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
    #[DeleteMapping('/tenant_api_interface')]
    #[Permission(code: 'tenant:tenant_api_interface:delete')]
    #[OperationLog('删除租户接口')]
    public function delete(Request $request): Response
    {
        $this->service->deleteById($request->all());
        return $this->success();
    }
}
