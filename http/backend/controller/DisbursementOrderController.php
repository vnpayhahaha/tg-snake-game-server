<?php

namespace http\backend\controller;

use app\controller\BasicController;
use app\exception\UnprocessableEntityException;
use app\lib\annotation\NoNeedLogin;
use app\lib\annotation\OperationLog;
use app\lib\annotation\Permission;
use app\lib\enum\ResultCode;
use app\router\Annotations\GetMapping;
use app\router\Annotations\PostMapping;
use app\router\Annotations\PutMapping;
use app\router\Annotations\RestController;
use app\service\DisbursementOrderService;
use DI\Attribute\Inject;
use support\Request;
use support\Response;

#[RestController("/admin/transaction")]
class DisbursementOrderController extends BasicController
{
    #[Inject]
    protected DisbursementOrderService $service;

    #[GetMapping('/disbursement_order/list')]
    #[Permission(code: 'transaction:disbursement_order:list')]
    #[OperationLog('收款订单列表')]
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

    // 核销
    #[Permission(code: 'transaction:disbursement_order:update')]
    #[OperationLog('核销付款订单')]
    #[PutMapping('/disbursement_order/write_off/{id}')]
    public function writeOff(Request $request, int $id): Response
    {
        $validator = validate($request->all(), [
            'transaction_voucher_id' => ['required', 'integer', 'between:1,9999999999'],
        ]);
        if ($validator->fails()) {
            throw new UnprocessableEntityException(ResultCode::UNPROCESSABLE_ENTITY, $validator->errors()->first());
        }
        $validatedData = $validator->validate();
        return $this->service->writeOff($id, $validatedData['transaction_voucher_id']) ? $this->success() : $this->error();
    }

    #[PutMapping('/disbursement_order/cancel')]
    #[Permission(code: 'transaction:disbursement_order:update')]
    #[OperationLog('取消付款订单')]
    public function cancel(Request $request): Response
    {
        $validator = validate($request->all(), [
            'data' => ['required', 'array'],
        ]);
        if ($validator->fails()) {
            throw new UnprocessableEntityException(ResultCode::UNPROCESSABLE_ENTITY, $validator->errors()->first());
        }
        $validatedData = $validator->validate();
        $user = $request->user;
        $updateNum = $this->service->cancelById($validatedData['data'], $user['id'], $user['username'], $request->requestId);
        return $updateNum ? $this->success() : $this->error();
    }

    // distribute
    #[PutMapping('/disbursement_order/distribute')]
    #[Permission(code: 'transaction:disbursement_order:update')]
    #[OperationLog('分配订单')]
    public function distribute(Request $request): Response
    {
        $validator = validate($request->post(), [
            'ids'                     => 'required|array',
            'ids.*'                   => 'required|integer|max:9999999999',
            'disbursement_channel_id' => 'required|integer|max:9999999999',
            'channel_type'            => [
                'required',
                'in:1,2'
            ],
            'bank_account_id'         => [
                'required_if:channel_type,1',
                'integer',
                'max:9999999999'
            ],
            'channel_account_id'      => [
                'required_if:channel_type,2',
                'integer',
                'max:9999999999'
            ]
        ]);
        if ($validator->fails()) {
            throw new UnprocessableEntityException(ResultCode::UNPROCESSABLE_ENTITY, $validator->errors()->first());
        }
        $validatedData = $validator->validate();
        $user = $request->user;
        $updateNum = $this->service->distribute($validatedData, $user['id'], $user['username'], $request->requestId);
        return $updateNum ? $this->success() : $this->error();
    }

    // 下载银行账单
    #[PostMapping('/disbursement_order/download_bank_bill')]
    public function downloadBankBill(Request $request): Response
    {
        $user = $request->user;
        return $this->service->downloadBankBill($request->all(), $user['id'], $user['username'], $request->requestId);
    }


    // 回调通知
    #[GetMapping('/disbursement_order/manual_notify/{id}')]
    #[OperationLog('付款订单手动回调')]
    public function notify(Request $request, int $id): Response
    {
        return $this->service->manualNotify($id) ? $this->success() : $this->error();
    }
}
