<?php

namespace http\backend\controller;

use app\controller\BasicController;
use app\exception\UnprocessableEntityException;
use app\lib\annotation\OperationLog;
use app\lib\annotation\Permission;
use app\lib\enum\ResultCode;
use app\router\Annotations\GetMapping;
use app\router\Annotations\PutMapping;
use app\router\Annotations\RestController;
use app\service\CollectionOrderService;
use DI\Attribute\Inject;
use support\Request;
use support\Response;

#[RestController("/admin/transaction")]
class CollectionOrderController extends BasicController
{
    #[Inject]
    protected CollectionOrderService $service;

    #[GetMapping('/collection_order/list')]
    #[Permission(code: 'transaction:collection_order:list')]
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
    #[Permission(code: 'transaction:collection_order:update')]
    #[OperationLog('核销收款订单')]
    #[PutMapping('/collection_order/write_off/{id}')]
    public function writeOff(Request $request, int $id): Response
    {
        $validator = validate($request->all(), [
            'transaction_voucher_id' => [
                'required',
                'integer',
                'between:1,9999999999'
            ],
        ]);
        if ($validator->fails()) {
            throw new UnprocessableEntityException(ResultCode::UNPROCESSABLE_ENTITY, $validator->errors()->first());
        }
        $validatedData = $validator->validate();
        return $this->service->writeOff($id, $validatedData['transaction_voucher_id']) ? $this->success() :
            $this->error();
    }

    #[PutMapping('/collection_order/cancel')]
    #[Permission(code: 'transaction:collection_order:update')]
    #[OperationLog('取消收款订单')]
    public function cancel(Request $request): Response
    {
        $validator = validate($request->all(), [
            'data' => [
                'required',
                'array'
            ],
        ]);
        if ($validator->fails()) {
            throw new UnprocessableEntityException(ResultCode::UNPROCESSABLE_ENTITY, $validator->errors()->first());
        }
        $validatedData = $validator->validate();
        $user = $request->user;
        $this->service->cancelById($validatedData['data'], $user['id'], $user['username'], $request->requestId);
        return $this->success();
    }

    // 回调通知
    #[GetMapping('/collection_order/manual_notify/{id}')]
    #[OperationLog('收款订单手动回调')]
    public function notify(Request $request, int $id): Response
    {
        return $this->service->manualNotify($id) ? $this->success() : $this->error();
    }
}
