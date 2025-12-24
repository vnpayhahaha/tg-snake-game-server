<?php

namespace http\backend\controller;

use app\controller\BasicController;
use app\lib\annotation\NoNeedLogin;
use app\lib\enum\ResultCode;
use app\lib\JwtAuth\facade\JwtAuth;
use app\model\enums\UserType;
use app\router\Annotations\GetMapping;
use app\router\Annotations\PostMapping;
use app\router\Annotations\RestController;
use DI\Attribute\Inject;
use http\backend\Service\PassportService;
use Illuminate\Support\Arr;
use support\Context;
use support\Request;
use support\Response;

#[RestController("/admin/passport")]
class PassportController extends BasicController
{

    #[Inject]
    protected PassportService $passportService;

    #[PostMapping('/login')]
    #[NoNeedLogin]
    public function login(Request $request): Response
    {
        $validator = validate($request->post(), [
            'username'        => 'required|string|max:20',
            'password'        => 'required|string|max:50',
            'google_2fa_code' => 'string|max:6'
        ]);
        if ($validator->fails()) {
            return $this->error(ResultCode::FAIL, $validator->errors()->first());
        }
        $validatedData = $validator->validate();
        $username = (string)$validatedData['username'];
        $password = (string)$validatedData['password'];
        $google_2fa_code = $validatedData['google_2fa_code'] ?? '';
        $browser = $request->header('User-Agent') ?: 'unknown';
        $os = $request->os();
        $result = $this->passportService->login(
            $username,
            $password,
            UserType::SYSTEM,
            $request->getRealIp(false),
            $browser,
            $os,
            $google_2fa_code
        );

        return $this->success($result);
    }

    #[PostMapping('/logout')]
    public function logout(Request $request): Response
    {

        $token = Context::get('token');
        if (!$token) {
            return $this->error(ResultCode::FAIL, 'Logout failed');
        }
        $isLogout = $this->passportService->logout($token);
        if (!$isLogout) {
            return $this->error(ResultCode::FAIL, 'Logout failed');
        }
        return $this->success();
    }

    #[GetMapping('/getInfo')]
    public function getInfo(Request $request): Response
    {
        $user = $request->user;
        return $this->success(Arr::only(
            $user?->toArray() ?: [],
            [
                'is_super_admin',
                'username',
                'nickname',
                'avatar',
                'signed',
                'backend_setting',
                'phone',
                'email',
                'is_enabled_google',
                'is_bind_google'
            ]
        ));
    }

    #[PostMapping('/refresh')]
    public function refresh(Request $request): Response
    {
        $token = Context::get('token');
        return $this->success([
            'access_token' => JwtAuth::refresh($token)->toString(),
            'token_type'   => JwtAuth::getConfig('backend')->getType(),
            'expire_at'    => JwtAuth::getConfig('backend')->getExpires(),
            'refresh_at'   => JwtAuth::getConfig('backend')->getRefreshTTL(),
        ]);
    }

}
