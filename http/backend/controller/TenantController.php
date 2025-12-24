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
use app\service\TenantService;
use DI\Attribute\Inject;
use support\Request;
use support\Response;

#[RestController("/admin/tenant")]
class TenantController extends BasicController
{
    #[Inject]
    protected TenantService $service;

    #[GetMapping('/tenant/list')]
    #[Permission(code: 'tenant:tenant:list')]
    #[OperationLog('租户管理列表')]
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
    #[DeleteMapping('/tenant/real_delete')]
    #[Permission(code: 'tenant:tenant:realDelete')]
    #[OperationLog('清空回收站')]
    public function realDelete(Request $request): Response
    {
        return $this->service->realDelete((array)$request->all()) ? $this->success() : $this->error();
    }

    // 单个或批量恢复在回收站的数据
    #[PutMapping('/tenant/recovery')]
    #[Permission(code: 'tenant:tenant:recovery')]
    #[OperationLog('租户回收站恢复')]
    public function recovery(Request $request): Response
    {
        return $this->service->recovery((array)$request->input('ids', [])) ? $this->success() : $this->error();
    }

    #[PostMapping('/tenant')]
    #[Permission(code: 'tenant:tenant:create')]
    #[OperationLog('创建租户')]
    public function create(Request $request): Response
    {
        $validator = validate($request->all(), [
            'company_name'          => 'required|string|max:200',
            'contact_user_name'     => 'required|string|max:20',
            'contact_phone'         => 'required|string|max:20',
            'user_num_limit'        => ['required', 'integer', 'between:-1,99'],
            'app_num_limit'         => ['required', 'integer', 'between:-1,99'],
            'is_enabled'            => ['required', 'boolean'],
            'safe_level'            => ['required', 'integer', 'between:0,99'],
            'settlement_type'       => ['required', 'integer', 'between:1,3'],
            'settlement_delay_days' => ['integer', 'between:0,99'],
            'auto_transfer'         => ['required', 'boolean'],
            'receipt_fee_type'      => ['array'],
            'receipt_fixed_fee'     => ['numeric', 'between:0,100'],
            'receipt_fee_rate'      => ['numeric', 'between:0,100'],
            'payment_fee_type'      => ['array'],
            'payment_fixed_fee'     => ['numeric', 'between:0,100'],
            'payment_fee_rate'      => ['numeric', 'between:0,100'],
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

    #[PutMapping('/tenant/{id}')]
    #[Permission(code: 'tenant:tenant:update')]
    #[OperationLog('编辑租户')]
    public function update(Request $request, int $id): Response
    {
        $validator = validate($request->all(), [
            'contact_user_name'        => 'required|string|max:20',
            'contact_phone'            => 'required|string|max:20',
            'company_name'             => 'required|string|max:200',
            'user_num_limit'           => ['required', 'integer', 'between:-1,99'],
            'app_num_limit'            => ['required', 'integer', 'between:-1,99'],
            'is_enabled'               => ['required', 'boolean'],
            'safe_level'               => ['required', 'integer', 'between:0,99'],
            'settlement_delay_days'    => ['integer', 'between:0,99'],
            'auto_transfer'            => ['required', 'boolean'],
            'receipt_fee_type'         => [['nullable', 'array']],
            'receipt_fixed_fee'        => ['numeric', 'between:0,100'],
            'receipt_fee_rate'         => ['numeric', 'between:0,100'],
            'payment_fee_type'         => [['nullable', 'array']],
            'payment_fixed_fee'        => ['numeric', 'between:0,100'],
            'payment_fee_rate'         => ['numeric', 'between:0,100'],
            'is_receipt'               => 'boolean',
            'is_payment'               => 'boolean',
            'receipt_min_amount'       => ['numeric', 'between:0,999999'],
            'receipt_max_amount'       => ['numeric', 'between:0,999999'],
            'payment_min_amount'       => ['numeric', 'between:0,999999'],
            'payment_max_amount'       => ['numeric', 'between:0,999999'],
            'receipt_settlement_type'  => ['required', 'integer', 'between:1,2'],
            'upstream_enabled'         => 'boolean',
            'float_enabled'            => 'boolean',
            'float_range'              => ['nullable', 'array'],
            'notify_range'             => ['nullable', 'array'],
            'auto_assign_enabled'      => 'boolean',
            'receipt_expire_minutes'   => ['integer', 'between:0,9999'],
            'payment_expire_minutes'   => ['integer', 'between:0,9999'],
            'reconcile_retain_minutes' => ['integer', 'between:0,9999'],
            'bill_delay_minutes'       => ['integer', 'between:0,9999'],
            'card_acquire_type'        => ['integer', 'between:1,3'],
            'auto_verify_fail_rate'    => ['numeric', 'between:0,100'],
            'upstream_items'           => ['nullable', 'array'],
            'payment_assign_items'     => ['nullable', 'array'],
            'collection_use_method'    => ['nullable', 'array'],
            'settlement_delay_mode'    => ['integer', 'between:1,3'],
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

    // 删除
    #[DeleteMapping('/tenant')]
    #[Permission(code: 'tenant:tenant:delete')]
    #[OperationLog('删除租户')]
    public function delete(Request $request): Response
    {
        $this->service->deleteById($request->all());
        return $this->success();
    }

    #[GetMapping('/tenant_dict/remote')]
    public function remote(Request $request): Response
    {
        $fields = [
            'id',
            'tenant_id',
            'company_name',
            'contact_user_name',
            'is_enabled',
            'created_by',
            'expired_at',
        ];
        return $this->success(
            $this->service->getList($request->all())->map(static fn($model) => $model->only($fields))
        );
    }

}
