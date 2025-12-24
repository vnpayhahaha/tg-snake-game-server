<?php

namespace http\backend\controller;

use app\constants\TransactionVoucher;
use app\controller\BasicController;
use app\exception\UnprocessableEntityException;
use app\lib\annotation\OperationLog;
use app\lib\annotation\Permission;
use app\lib\enum\ResultCode;
use app\router\Annotations\GetMapping;
use app\router\Annotations\PostMapping;
use app\router\Annotations\PutMapping;
use app\router\Annotations\RestController;
use app\service\TransactionVoucherService;
use DI\Attribute\Inject;
use support\Request;
use support\Response;

#[RestController("/admin/transaction")]
class TransactionVoucherController extends BasicController
{
    #[Inject]
    protected TransactionVoucherService $service;

    #[GetMapping('/transaction_voucher/list')]
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
    #[PostMapping('/transaction_voucher')]
    #[Permission(code: 'transaction:transaction_voucher:create')]
    #[OperationLog('新增交易凭证')]
    public function create(Request $request): Response
    {
        $validator = validate($request->all(), [
            'channel_id'               => 'required|integer|between:1,4294967295',
            'bank_account_id'          => [
                'integer',
                'between:1,4294967295',
            ],
            'channel_account_id'       => [
                'integer',
                'between:1,4294967295',
            ],
            'collection_amount'        => [
                'required',
                'numeric',
                'between:0.01,999999'
            ],
            'transaction_voucher'      => [
                'required',
                'string',
                'max:255',
                'min:1',
            ],
            'transaction_voucher_type' => [
                'required',
                'integer',
                'between:1,5',
            ],
            'transaction_type'         => [
                'required',
                'integer',
                'between:1,2',
            ],
        ]);
        if ($validator->fails()) {
            throw new UnprocessableEntityException(ResultCode::UNPROCESSABLE_ENTITY, $validator->errors()->first());
        }
        $validatedData = $validator->validate();
        return $this->service->create(array_merge(
            $validatedData + [
                'collection_source' => TransactionVoucher::COLLECTION_SOURCE_MANUAL,
                'content'           => json_encode($validatedData)
            ],
            [
                'operation_admin_id' => $request->user->id,
            ]
        ))
            ? $this->success()
            : $this->error();
    }

    // PutMapping
    #[PutMapping('/transaction_voucher/{id}')]
    #[Permission(code: 'transaction:transaction_voucher:update')]
    #[OperationLog('编辑交易凭证')]
    public function update(Request $request, int $id): Response
    {
        $validator = validate($request->all(), [
            'channel_id'               => 'required|integer|between:1,4294967295',
            'bank_account_id'          => [
                'integer',
                'between:1,4294967295',
            ],
            'channel_account_id'       => [
                'integer',
                'between:1,4294967295',
            ],
            'collection_amount'        => [
                'required',
                'numeric',
                'between:0.01,999999'
            ],
            'transaction_voucher'      => [
                'required',
                'string',
                'max:255',
                'min:1',
            ],
            'transaction_voucher_type' => [
                'required',
                'integer',
                'between:1,3',
            ],
            'transaction_type'         => [
                'required',
                'integer',
                'between:1,2',
            ],
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

    // 核销选择项，类型分组
    #[GetMapping('/transaction_voucher/write_off_options')]
    public function selectOfWriteOff(Request $request): Response
    {
        $list = $this->service->getWriteOffOptions($request->all());
        return $this->success($list);
    }
}
