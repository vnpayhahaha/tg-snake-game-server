<?php

namespace http\openapi\controller;

use app\constants\DisbursementOrder;
use app\controller\BasicController;
use app\exception\OpenApiException;
use app\lib\annotation\OperationLog;
use app\lib\annotation\Permission;
use app\lib\enum\ResultCode;
use app\router\Annotations\GetMapping;
use app\router\Annotations\PostMapping;
use app\router\Annotations\RestController;
use app\service\DisbursementOrderService;
use app\service\TenantApiInterfaceService;
use app\service\TenantService;
use DI\Attribute\Inject;
use support\Request;
use support\Response;
use Webman\RateLimiter\Annotation\RateLimiter;
use Webman\RateLimiter\Limiter;

#[RestController("/v1/api/disbursement")]
class DisbursementOrderController extends BasicController
{
    #[Inject]
    protected DisbursementOrderService $service;
    #[Inject]
    protected TenantService $tenantService;
    #[Inject]
    protected TenantApiInterfaceService $tenantApiInterfaceService;

    #[GetMapping('/list')]
    #[OperationLog('付款订单列表')]
    public function pageList(Request $request): Response
    {
        $params = $request->all();
        $params['orderBy'] = 'id';
        $params['orderType'] = 'desc';
        return $this->success(
            data: $this->service->page(
                $params,
                $this->getCurrentPage(),
                $this->getPageSize(),
            )
        );
    }

    #[PostMapping('/create_order')]
    #[RateLimiter(limit: 100, ttl: 60, key: RateLimiter::UID)]
    public function create_order(Request $request): Response
    {
        $params = $request->all();
        // 判断短时间内重复请求10s
        if (isset($params['tenant_order_no']) && filled($params['tenant_order_no'])) {
            Limiter::check('disbursement_order_create_' . $params['tenant_order_no'], 1, 10, trans('repeatedly', [':attribute' => 'tenant_order_no'], 'validation'));
        }
        $rate_limit = $this->tenantApiInterfaceService->getRateLimitByApiName('disbursement_order_create');
        Limiter::check('disbursement_order_create', $rate_limit, 1);
        // 参数验证
        $validator = validate($request->all(), [
            'tenant_id'          => [
                'required',
                'string',
                'max:20',
                function ($attribute, $value, $fail) use ($request) {
                    $findTenant = $this->tenantService->repository->getQuery()->select(['tenant_id', 'is_enabled', 'is_receipt'])->where('tenant_id', $value)->first();
                    if (!$findTenant) {
                        return $fail(trans('exists', [':attribute' => $attribute], 'validation'));
                    }
                    if ($findTenant->is_enabled === false || $findTenant->is_payment === false) {
                        return $fail(trans('discontinued', [':attribute' => $attribute], 'validation'));
                    }
                }
            ],
            'app_key'            => 'required|string|max:16',
            'tenant_order_no'    => [
                'required',
                'string',
                'max:64',
                function ($attribute, $value, $fail) use ($request) {
                    if ($this->service->repository->getQuery()->where('tenant_order_no', $value)->exists()) {
                        $fail(trans('unique', [':attribute' => $attribute], 'validation'));
                    }
                }
            ],
            'amount'             => 'required|numeric|min:0.01',
            'notify_url'         => 'string|active_url|max:255',
            'notify_remark'      => 'string|max:255',
            'payment_type'       => ['required', 'integer', 'in:' . DisbursementOrder::PAYMENT_TYPE_BANK_CARD . ',' . DisbursementOrder::PAYMENT_TYPE_UPI],
            'payee_bank_name'    => [
                'required_if:payment_type,' . DisbursementOrder::PAYMENT_TYPE_BANK_CARD,
                'string',
                'max:100',
            ],
            'payee_bank_code'    => [
                'required_if:payment_type,' . DisbursementOrder::PAYMENT_TYPE_BANK_CARD,
                'string',
                'max:100',
            ],
            'payee_account_name' => [
                'required_if:payment_type,' . DisbursementOrder::PAYMENT_TYPE_BANK_CARD,
                'string',
                'max:100',
            ],
            'payee_account_no'   => [
                'required_if:payment_type,' . DisbursementOrder::PAYMENT_TYPE_BANK_CARD,
                'string',
                'max:100',
            ],
            'payee_phone'        => [
                'string',
                'max:20',
            ],
            'payee_upi'          => [
                'required_if:payment_type,' . DisbursementOrder::PAYMENT_TYPE_UPI,
                'string',
                'max:100',
                'email',
            ],
        ]);
        if ($validator->fails()) {
            throw new OpenApiException(ResultCode::UNPROCESSABLE_ENTITY, $validator->errors()->first());
        }
        $validatedData = $validator->validate();
        $successData = $this->service->createOrder($validatedData, $validatedData['app_key']);
        return $this->success($successData);
    }

    // 根据订单号查询订单
    #[PostMapping('/query_order')]
    public function query_order(Request $request): Response
    {
        $params = $request->all();
        // 参数验证
        $validator = validate($params, [
            'tenant_id'         => [
                'required',
                'string',
                'max:20',
                function ($attribute, $value, $fail) use ($params) {
                    if ((
                            !isset($params['tenant_order_no']) ||
                            !filled($params['tenant_order_no'])
                        ) &&
                        (
                            !isset($params['platform_order_no']) ||
                            !filled($params['platform_order_no'])
                        )) {
                        $fail(trans('required_if_declined', [
                            ':other'     => 'tenant_order_no',
                            ':attribute' => 'platform_order_no'
                        ], 'validation'));
                    }
                }
            ],
            'tenant_order_no'   => [
                'string',
                'max:64',
            ],
            'platform_order_no' => [
                'string',
                'max:32',
            ],
        ]);
        if ($validator->fails()) {
            throw new OpenApiException(ResultCode::UNPROCESSABLE_ENTITY, $validator->errors()->first());
        }
        $validatedData = $validator->validate();
        $orderFind = $this->service->repository->getQuery()
            ->select([
                'id',
                'tenant_id',
                'tenant_order_no',
                'platform_order_no',
                'amount',
                'utr',
                'status',
                'pay_time',
                'notify_url',
                'notify_status',
                'created_at',
            ])
            ->where('tenant_id', $validatedData['tenant_id'])
            ->where(function ($query) use ($validatedData) {
                if (isset($validatedData['tenant_order_no'])) {
                    $query->where('tenant_order_no', $validatedData['tenant_order_no']);
                }
                if (isset($validatedData['platform_order_no'])) {
                    $query->where('platform_order_no', $validatedData['platform_order_no']);
                }
            })
            ->first();
        if (!$orderFind) {
            return $this->error(ResultCode::ORDER_NOT_FOUND);
        }
        return $this->success($orderFind->makeHidden(['id'])->toArray());
    }
}
