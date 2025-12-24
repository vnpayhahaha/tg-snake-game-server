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
use app\service\TenantAccountService;
use DI\Attribute\Inject;
use PragmaRX\Google2FA\Google2FA;
use support\Request;
use support\Response;
use Webman\RateLimiter\Annotation\RateLimiter;

#[RestController("/admin/tenant")]
class TenantAccountController extends BasicController
{
    #[Inject]
    protected TenantAccountService $service;

    #[Inject]
    protected Google2FA $google2FA;

    #[GetMapping('/tenant_account/list')]
    #[Permission(code: 'tenant:tenant_account:list')]
    #[OperationLog('租户账户列表')]
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

    // change balance_available
    #[PutMapping('/tenant_account/change_balance_available')]
    #[Permission(code: 'tenant:tenant_account:update')]
    #[OperationLog('改变可用余额')]
    #[RateLimiter(limit: 1, ttl: 1)]
    public function change_balance_available(Request $request): Response
    {
        $validator = validate($request->all(), [
            'id'                => [
                'required',
                'integer',
                function ($attribute, $value, $fail) {
                    if (!$this->service->repository->getModel()->where($attribute, $value)->exists()) {
                        $fail(trans('exists', [':attribute' => $attribute], 'validation'));
                    }
                },
            ],
            // change_amount 不能等于0
            'change_amount' => [
                'required',
                'numeric',
                function ($attribute, $value, $fail) {
                    if ($value == 0) {
                        $fail(trans('not_equal', [':attribute' => $attribute,':value' => $value], 'validation'));
                    }
                },
            ],
            'google2f_code' => 'string|nullable'
        ]);
        if ($validator->fails()) {
            throw new UnprocessableEntityException(ResultCode::UNPROCESSABLE_ENTITY, $validator->errors()->first());
        }
        $validatedData = $validator->validate();
        // 验证 google2f_code
        if(isset($validatedData['google2f_code']) && filled($validatedData['google2f_code'])){
            $user = $request->user;
            $is_pass = $this->google2FA->verifyKey($user->google_secret, $validatedData['google2f_code']);
            if(!$is_pass){
                return $this->error(ResultCode::USER_GOOGLE_2FA_VERIFY_FAILED);
            }
        }
        return $this->service->changeBalanceAvailable($validatedData['id'], $validatedData['change_amount']) ? $this->success() : $this->error();
    }

    // change balance_frozen
    #[PutMapping('/tenant_account/change_balance_frozen')]
    #[Permission(code: 'tenant:tenant_account:update')]
    #[OperationLog('改变冻结余额')]
    #[RateLimiter(limit: 1, ttl: 1, key: RateLimiter::SID)]
    public function change_balance_frozen(Request $request): Response
    {
        $validator = validate($request->all(), [
            'id'                => [
                'required',
                'integer',
                function ($attribute, $value, $fail) {
                    if (!$this->service->repository->getModel()->where($attribute, $value)->exists()) {
                        $fail(trans('exists', [':attribute' => $attribute], 'validation'));
                    }
                },
            ],
            // change_amount 不能等于0
            'change_amount' => [
                'required',
                'numeric',
                function ($attribute, $value, $fail) {
                    if ($value == 0) {
                        $fail(trans('not_equal', [':attribute' => $attribute,':value' => $value], 'validation'));
                    }
                },
            ],
            'google2f_code' => 'string|nullable'
        ]);
        if ($validator->fails()) {
            throw new UnprocessableEntityException(ResultCode::UNPROCESSABLE_ENTITY, $validator->errors()->first());
        }
        $validatedData = $validator->validate();
        // 验证 google2f_code
        if(isset($validatedData['google2f_code']) && filled($validatedData['google2f_code'])){
            $user = $request->user;
            $is_pass = $this->google2FA->verifyKey($user->google_secret, $validatedData['google2f_code']);
            if(!$is_pass){
                return $this->error(ResultCode::USER_GOOGLE_2FA_VERIFY_FAILED);
            }
        }
        return $this->service->changeBalanceFrozen($validatedData['id'], $validatedData['change_amount']) ? $this->success() : $this->error();
    }

    #[PutMapping('/tenant_account/{id}')]
    #[Permission(code: 'tenant:tenant_account:update')]
    #[OperationLog('编辑租户账户')]
    public function update(Request $request, int $id): Response
    {
        $validator = validate($request->all(), [
            'account_type' => ['required', 'integer', 'between:1,2'],
            'tenant_id'    => 'required|string|max:20',
        ]);
        if ($validator->fails()) {
            return $this->error(ResultCode::UNPROCESSABLE_ENTITY, $validator->errors()->first());
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


}
