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
use app\service\BankAccountService;
use DI\Attribute\Inject;
use support\Request;
use support\Response;

#[RestController("/admin/channel")]
class BankAccountController extends BasicController
{
    #[Inject]
    protected BankAccountService $service;

    #[GetMapping('/bank_account/list')]
    #[Permission(code: 'channel:bank_account:list')]
    #[OperationLog('银行账号列表')]
    public function pageList(Request $request): Response
    {
        return $this->success(
            $this->service->page($request->all(), $this->getCurrentPage(), $this->getPageSize())
        );
    }


    // 单个或批量真实删除数据 （清空回收站）
    #[DeleteMapping('/bank_account/real_delete')]
    #[Permission(code: 'channel:bank_account:real_delete')]
    #[OperationLog('清空银行账户')]
    public function real_delete(Request $request): Response
    {
        return $this->service->realDelete((array)$request->all()) ? $this->success() : $this->error();
    }

    // 单个或批量恢复在回收站的数据
    #[PutMapping('/bank_account/recovery')]
    #[Permission(code: 'channel:bank_account:recovery')]
    #[OperationLog('银行账户回收站恢复')]
    public function recovery(Request $request): Response
    {
        return $this->service->recovery((array)$request->input('ids', [])) ? $this->success() : $this->error();
    }

    #[PostMapping('/bank_account')]
    #[Permission(code: 'channel:bank_account:create')]
    #[OperationLog('创建银行账户')]
    public function create(Request $request): Response
    {
        $validator = validate($request->all(), [
            'channel_id'              => ['required', 'integer', 'between:1,999999999'],
            'branch_name'             => 'required|string|max:100',
            'account_holder'          => 'required|string|max:100',
            'account_number'          => 'string|max:50',
            'upi_id'                  => 'string|max:100',
            'bank_code'               => 'string|max:20',
            'balance'                 => 'required|numeric|between:0,999999999',
            'float_amount_enabled'    => ['required', 'boolean'],
            'daily_max_receipt'       => 'required|numeric|between:0,999999999',
            'daily_max_payment'       => 'required|numeric|between:0,999999999',
            'daily_max_receipt_count' => 'required|integer|between:0,999999999',
            'daily_max_payment_count' => 'required|integer|between:0,999999999',
            'max_receipt_per_txn'     => 'required|numeric|between:0,999999999',
            'max_payment_per_txn'     => 'required|numeric|between:0,999999999',
            'min_receipt_per_txn'     => 'required|numeric|between:0,999999999',
            'min_payment_per_txn'     => 'required|numeric|between:0,999999999',
            'security_level'          => 'required|integer|between:1,99',
            'limit_quota'             => 'required|numeric|between:0,999999999',
            'status'                  => ['required', 'boolean'],
            'support_collection'      => ['required', 'boolean'],
            'support_disbursement'    => ['required', 'boolean'],
            'down_bill_template_id'   => ['array'],
            'down_bill_template_id.*' => ['string', 'max:20'],
            'account_config'          => ['array'],
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

    #[PutMapping('/bank_account/{id}')]
    #[Permission(code: 'channel:bank_account:update')]
    #[OperationLog('编辑银行账户')]
    public function update(Request $request, int $id): Response
    {
        $validator = validate($request->all(), [
            'channel_id'              => ['required', 'integer', 'between:1,999999999'],
            'branch_name'             => 'required|string|max:100',
            'account_holder'          => 'required|string|max:100',
            'account_number'          => 'string|max:50',
            'upi_id'                  => 'string|max:100',
            'bank_code'               => 'string|max:20',
            'balance'                 => 'required|numeric|between:0,999999999',
            'float_amount_enabled'    => ['required', 'boolean'],
            'daily_max_receipt'       => 'required|numeric|between:0,999999999',
            'daily_max_payment'       => 'required|numeric|between:0,999999999',
            'daily_max_receipt_count' => 'required|integer|between:0,999999999',
            'daily_max_payment_count' => 'required|integer|between:0,999999999',
            'max_receipt_per_txn'     => 'required|numeric|between:0,999999999',
            'max_payment_per_txn'     => 'required|numeric|between:0,999999999',
            'min_receipt_per_txn'     => 'required|numeric|between:0,999999999',
            'min_payment_per_txn'     => 'required|numeric|between:0,999999999',
            'security_level'          => 'required|integer|between:1,99',
            'limit_quota'             => 'required|numeric|between:0,999999999',
            'status'                  => ['required', 'boolean'],
            'support_collection'      => ['required', 'boolean'],
            'support_disbursement'    => ['required', 'boolean'],
            'down_bill_template_id'   => ['array'],
            'down_bill_template_id.*' => ['string', 'max:20'],
            'account_config'          => ['array'],
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

    #[DeleteMapping('/bank_account')]
    #[Permission(code: 'channel:bank_account:delete')]
    #[OperationLog('删除银行账户')]
    public function delete(Request $request): Response
    {
        $this->service->deleteById($request->all());
        return $this->success();
    }

    #[GetMapping('/bank_account/remote')]
    public function remote(Request $request): Response
    {
        $fields = [
            'id',
            'channel_id',
            'branch_name',
            'account_holder',
            'account_number',
            'status',
            'bank_code',
            'support_collection',
            'support_disbursement',
        ];
        return $this->success(
            $this->service->getList($request->all())->map(static fn($model) => $model->only($fields))
        );
    }

    // 获取付款账单模板ID项by账户ID
    #[GetMapping('/down_bill_template_ids/{id}')]
    public function getDownBillTemplateIds(Request $request, int $id): Response
    {
        return $this->success(
            $this->service->getDownBillTemplateIds($id)
        );
    }
}
