<?php

namespace http\tenant\controller;

use app\controller\BasicController;
use app\exception\UnprocessableEntityException;
use app\lib\enum\ResultCode;
use app\router\Annotations\GetMapping;
use app\router\Annotations\PutMapping;
use app\router\Annotations\RestController;
use app\service\TenantUserService;
use DI\Attribute\Inject;
use PragmaRX\Google2FA\Google2FA;
use support\Request;
use support\Response;

#[RestController("/tenant")]
class UserController  extends BasicController
{
    #[Inject]
    protected TenantUserService $userService;

    #[Inject]
    protected Google2FA $google2FA;

    #[GetMapping('/userDict/remote')]
    public function remote(Request $request): Response
    {
        $fields = [
            'id',
            'username',
            'status',
            'login_ip',
            'login_time',
        ];
        $user = $request->user;
        return $this->success(
            $this->userService->getList([
                'tenant_id' => $user->tenant_id,
            ])->map(static fn($user) => $user->only($fields))
        );
    }

    #[PutMapping('/bindGoogleSecretKey')]
    public function bindGoogleSecretKey(Request $request): Response
    {
        $validator = validate($request->all(), [
            'google_secret'  => 'required|string',
            'is_bind_google' => 'required|boolean',
            'code'           => 'required|string'
        ]);
        if ($validator->fails()) {
            throw new UnprocessableEntityException(ResultCode::UNPROCESSABLE_ENTITY, $validator->errors()->first());
        }
        $validatedData = $validator->validate();
        $is_pass = $this->google2FA->verifyKey($validatedData['google_secret'], $validatedData['code']);
        if ($is_pass) {
            unset($validatedData['code']);
            return $this->userService->repository->getQuery()->where('id', $request->user->id)->update($validatedData) > 0 ?
                $this->success() :
                $this->error(ResultCode::USER_GOOGLE_2FA_VERIFY_FAILED);
        }
        return $this->error(ResultCode::USER_GOOGLE_2FA_VERIFY_FAILED);
    }

    // 更新google验证状态
    #[PutMapping('/user/google_2fa_status')]
    public function updateGoogle2FAStatus(Request $request): Response
    {
        $validator = validate($request->all(), [
            'is_enabled_google' => 'required|boolean',
        ]);
        if ($validator->fails()) {
            throw new UnprocessableEntityException(ResultCode::UNPROCESSABLE_ENTITY, $validator->errors()->first());
        }
        $validatedData = $validator->validate();
        $res = $this->userService->repository->getQuery()
            ->where('id', $request->user->id)
            ->where('is_enabled_google', !$validatedData['is_enabled_google'])
            ->update($validatedData);

        return $res > 0 ? $this->success() : $this->error();
    }
}
