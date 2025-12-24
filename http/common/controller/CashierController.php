<?php

namespace http\common\controller;

use app\controller\BasicController;
use app\exception\OpenApiException;
use app\exception\UnprocessableEntityException;
use app\lib\enum\ResultCode;
use app\model\ModelCollectionOrder;
use app\router\Annotations\GetMapping;
use app\router\Annotations\PutMapping;
use app\router\Annotations\RestController;
use app\service\CollectionOrderService;
use DI\Attribute\Inject;
use support\Request;
use support\Response;

#[RestController("/v1/common/collection")]
class CashierController extends BasicController
{
    #[Inject]
    protected CollectionOrderService $service;

    // query order
    #[GetMapping('/query_order')]
    public function query_order(Request $request): Response
    {
        $params = $request->all();
        // 参数验证
        $validator = validate($params, [
            'platform_order_no' => [
                'required',
                'string',
                'max:32',
            ],
        ]);
        if ($validator->fails()) {
            throw new OpenApiException(ResultCode::UNPROCESSABLE_ENTITY, $validator->errors()->first());
        }
        $validatedData = $validator->validate();
        /** @var ModelCollectionOrder $orderFind */
        $orderFind = $this->service->repository->getQuery()
            ->select([
                'id',
                'tenant_order_no',
                'platform_order_no',
                'amount',
                'payable_amount',
                'status',
                'pay_time',
                'payer_upi',
                'expire_time',
                'return_url',
                'created_at',
            ])
            ->where(function ($query) use ($validatedData) {
                if (isset($validatedData['platform_order_no'])) {
                    $query->where('platform_order_no', $validatedData['platform_order_no']);
                }
            })
            ->first();
        if (!$orderFind) {
            return $this->error(ResultCode::ORDER_NOT_FOUND);
        }
        $formatCreatOrderResult = $this->service->formatCreatOrderResult($orderFind);
        return $this->success($formatCreatOrderResult);
    }

    #[PutMapping('/submitted_utr')]
    public function submittedUtr(Request $request): Response
    {
        $validator = validate($request->all(), [
            'platform_order_no'      => [
                'required',
                'string',
                'max:32',
            ],
            'customer_submitted_utr' => 'required|string|max:20',
        ]);
        if ($validator->fails()) {
            throw new UnprocessableEntityException(ResultCode::UNPROCESSABLE_ENTITY, $validator->errors()->first());
        }
        $validatedData = $validator->validate();
        // 查询订单
        $order = $this->service->repository->getModel()->where('platform_order_no', $validatedData['platform_order_no'])->first();
        if (!$order) {
            throw new UnprocessableEntityException(ResultCode::ORDER_NOT_FOUND);
        }
        $order->customer_submitted_utr = $validatedData['customer_submitted_utr'];
        return $order->save() ? $this->success() : $this->error();
    }
}