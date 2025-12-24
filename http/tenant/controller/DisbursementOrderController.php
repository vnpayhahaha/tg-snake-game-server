<?php

namespace http\tenant\controller;

use app\constants\DisbursementOrder;
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
use http\tenant\Service\DisbursementOrderService;
use app\service\TenantService;
use DI\Attribute\Inject;
use PragmaRX\Google2FA\Google2FA;
use support\Request;
use support\Response;
use Webman\RateLimiter\Annotation\RateLimiter;
use Webman\RateLimiter\Limiter;

#[RestController("/tenant/transaction")]
class DisbursementOrderController extends BasicController
{
    #[Inject]
    protected DisbursementOrderService $service;
    #[Inject]
    protected TenantService $tenantService;
    #[Inject]
    protected Google2FA $google2FA;

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

    #[PostMapping('/disbursement_order')]
    #[RateLimiter(limit: 100, ttl: 60, key: RateLimiter::UID)]
    public function create_order(Request $request): Response
    {
        $params = $request->all();
        // 判断短时间内重复请求10s
        if (isset($params['tenant_order_no']) && filled($params['tenant_order_no'])) {
            Limiter::check('disbursement_order_create_' . $params['tenant_order_no'], 1, 10, trans('repeatedly', [':attribute' => 'tenant_order_no'], 'validation'));
        }
        Limiter::check('disbursement_order_create', 2, 1);
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
                    if ($findTenant->is_enabled === false || $findTenant->is_receipt === false) {
                        return $fail(trans('discontinued', [':attribute' => $attribute], 'validation'));
                    }
                }
            ],
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
            'google2f_code'      => 'string|nullable|max:6'
        ]);
        if ($validator->fails()) {
            throw new OpenApiException(ResultCode::UNPROCESSABLE_ENTITY, $validator->errors()->first());
        }
        $validatedData = $validator->validate();
        $user = $request->user;
        // 验证 google2f_code
        if (isset($validatedData['google2f_code']) && filled($validatedData['google2f_code'])) {
            $is_pass = $this->google2FA->verifyKey($user->google_secret, $validatedData['google2f_code']);
            if (!$is_pass) {
                return $this->error(ResultCode::USER_GOOGLE_2FA_VERIFY_FAILED);
            }
        }
        $successData = $this->service->createOrder($validatedData, 'merchant end:' . $user->username . "[{$user->id}]");
        return $this->success($successData);
    }

    #[PutMapping('/disbursement_order/cancel')]
    #[OperationLog('客户取消付款订单')]
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
        $updateNum = $this->service->cancelByCustomerId($validatedData['data'], $user['tenant_id'], $user['id'], $user['username'], $request->requestId);
        return $updateNum ? $this->success() : $this->error();
    }
}
