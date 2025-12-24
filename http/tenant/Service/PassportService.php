<?php

namespace http\tenant\Service;


use app\exception\BusinessException;
use app\exception\UnprocessableEntityException;
use app\lib\enum\ResultCode;
use app\repository\TenantUserLoginLogRepository;
use app\repository\TenantUserRepository;
use app\service\IService;
use DI\Attribute\Inject;
use http\tenant\Event\Dto\UserLoginEventDto;
use PragmaRX\Google2FA\Google2FA;
use Webman\Event\Event;
use Workerman\Coroutine;

class PassportService extends IService
{
    #[Inject]
    protected TenantUserRepository $repository;

    #[Inject]
    protected TenantUserLoginLogRepository $userLoginLogRepository;
    #[Inject]
    protected Google2FA $google2FA;
    /**
     * @var string jwt场景
     */
    private string $jwt = 'tenant';

    /**
     * @param string $username
     * @param string $password
     * @param string $tenant_id
     * @param string $ip
     * @param string $browser
     * @param string $os
     * @return array
     */

    public function login(string $username, string $password, string $tenant_id, string $ip = '0.0.0.0', string $browser = 'unknown', string $os = 'unknown', string $google_2fa_code = ''): array
    {
        $user = $this->repository->findByUnameType($username, $tenant_id);
        if (!filled($user)) {
            throw new UnprocessableEntityException(ResultCode::USER_LOGIN_FAILED, trans('password_error', [], 'auth'));
        }
        // 验证$google_2fa_code
        if(filled($user->google_secret) && $user->is_bind_google && $user->is_enabled_google){
            if(!filled($google_2fa_code)){
                throw new UnprocessableEntityException(ResultCode::USER_LOGIN_FAILED, trans('user_google_2fa_verify_failed', [], 'result'));
            }
            $is_pass = $this->google2FA->verifyKey($user->google_secret, $google_2fa_code);
            if(!$is_pass){
                throw new UnprocessableEntityException(ResultCode::USER_LOGIN_FAILED, trans('user_google_2fa_verify_failed', [], 'result'));
            }
        }
        if (!$user->verifyPassword($password)) {
            var_dump('密码错误');
            Event::dispatch('tenant.user.login', new UserLoginEventDto($user, $ip, $os, $browser, false));
            throw new UnprocessableEntityException(ResultCode::USER_LOGIN_FAILED, trans('password_error', [], 'auth'));
        }

        if (!$user->status) {
            var_dump('用户被禁用');
            throw new BusinessException(ResultCode::DISABLED);
        }

        var_dump('用户登录成功');
        $jwt = user('tenant');
        $config = $jwt->getConfig('tenant');
        var_dump('==tenant==');
        $token = $jwt->token($user->id)->toString();
        Event::dispatch('tenant.user.login', new UserLoginEventDto($user, $ip, $os, $browser, true));
        return [
            'access_token' => $token,
            'token_type'   => $config->getType(),
            //            'refresh_token' => $jwt->refresh(),
            'expire_at'    => $config->getExpires(),
            'refresh_at'   => $config->getRefreshTTL(),
        ];
    }

    public function logout(string $token): bool
    {
        return user('tenant')->logout($token);
    }

    // 记录登录日志
    public function loginLog(UserLoginEventDto $event): void
    {
        $user = $event->getUser();
        Coroutine::create(fn() => $this->userLoginLogRepository->getModel()->create([
            'username'  => $user->username,
            'tenant_id' => $user->tenant_id,
            'ip'        => $event->getIp(),
            'os'        => $event->getOs(),
            'browser'   => $event->getBrowser(),
            'status'    => $event->isLogin() ? 1 : 2,
        ]));
    }
}
