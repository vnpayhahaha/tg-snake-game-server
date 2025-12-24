<?php

namespace http\tenant\controller;

use app\controller\BasicController;
use app\exception\OpenApiException;
use app\exception\UnprocessableEntityException;
use app\lib\annotation\OperationLog;
use app\lib\annotation\Permission;
use app\lib\enum\ResultCode;
use app\router\Annotations\GetMapping;
use app\router\Annotations\PostMapping;
use app\router\Annotations\PutMapping;
use app\router\Annotations\RestController;
use app\service\TenantService;
use DI\Attribute\Inject;
use http\tenant\Service\CollectionOrderService;
use support\Request;
use support\Response;
use Webman\RateLimiter\Annotation\RateLimiter;
use Webman\RateLimiter\Limiter;

#[RestController("/tenant/transaction")]
class CollectionOrderController extends BasicController
{
    #[Inject]
    protected CollectionOrderService $service;
    #[Inject]
    protected TenantService $tenantService;

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

    #[PostMapping('/collection_order')]
    #[RateLimiter(limit: 100, ttl: 60, key: RateLimiter::UID)]
    public function create_order(Request $request): Response
    {
        $params = $request->all();
        // 判断短时间内重复请求10s
        if (isset($params['tenant_order_no']) && filled($params['tenant_order_no'])) {
            Limiter::check('collection_order_create_' . $params['tenant_order_no'], 1, 10, trans('repeatedly', [':attribute' => 'tenant_order_no'], 'validation'));
        }
        Limiter::check('collection_order_create', 2, 1);
        // 参数验证
        $validator = validate($request->all(), [
            'tenant_id'       => [
                'required',
                'string',
                'max:20',
                function ($attribute, $value, $fail) use ($request) {
                    $findTenant = $this->tenantService->repository->getQuery()->select([
                        'tenant_id',
                        'is_enabled',
                        'is_receipt'
                    ])->where('tenant_id', $value)->first();
                    if (!$findTenant) {
                        return $fail(trans('exists', [':attribute' => $attribute], 'validation'));
                    }
                    if ($findTenant->is_enabled === false || $findTenant->is_receipt === false) {
                        return $fail(trans('discontinued', [':attribute' => $attribute], 'validation'));
                    }
                }
            ],
            'tenant_order_no' => [
                'required',
                'string',
                'max:64',
                function ($attribute, $value, $fail) use ($request) {
                    if ($this->service->repository->getQuery()->where('tenant_order_no', $value)->exists()) {
                        $fail(trans('unique', [':attribute' => $attribute], 'validation'));
                    }
                }
            ],
            'amount'          => 'required|numeric|min:0.01',
            'notify_url'      => 'string|active_url|max:255',
            'return_url'      => 'string|active_url|max:255',
            'notify_remark'   => 'string|max:255',
        ]);
        if ($validator->fails()) {
            throw new OpenApiException(ResultCode::UNPROCESSABLE_ENTITY, $validator->errors()->first());
        }
        $validatedData = $validator->validate();
        $user = $request->user;
        $successData = $this->service->createOrder($validatedData, 'merchant end:' . $user->username . "[{$user->id}]");
        // var_dump('订单创建结果：', $successData);
        if (filled($successData)) {
            return $this->success($successData);
        }
        return $this->error(ResultCode::ORDER_NO_AVAILABLE_COLLECTION_METHOD);
    }

    // cancel
    #[PutMapping('/collection_order/cancel')]
    #[Permission(code: 'transaction:collection_order:update')]
    #[OperationLog('取消收款订单')]
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
        $this->service->cancelByCustomerId($validatedData['data'], $user['tenant_id'], $user['id'], $user['username'], $request->requestId);
        return $this->success();
    }

    // submitted_utr
    #[PutMapping('/collection_order/{id}')]
    #[Permission(code: 'transaction:collection_order:update')]
    #[OperationLog('提交UTR')]
    public function submittedUtr(Request $request, int $id): Response
    {
        $validator = validate($request->all(), [
            'customer_submitted_utr' => 'required|string|max:20',
        ]);
        if ($validator->fails()) {
            throw new UnprocessableEntityException(ResultCode::UNPROCESSABLE_ENTITY, $validator->errors()->first());
        }
        $validatedData = $validator->validate();
        $this->service->updateById($id, $validatedData);
        return $this->success();
    }
}
