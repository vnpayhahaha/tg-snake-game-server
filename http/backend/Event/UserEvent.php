<?php

namespace http\backend\Event;

use app\lib\JwtAuth\event\EventHandler;
use app\lib\JwtAuth\facade\JwtAuth;
use Lcobucci\JWT\Token;
use support\Redis;

class UserEvent implements EventHandler
{
    public function login(Token $token)
    {
        // TODO: Implement login() method.
    }

    public function logout(Token $token)
    {
        $options = config('jwt.manager') ?? [];
        $blacklist_enabled = $options['blacklist_enabled'] ?? true;

        $parseToken = JwtAuth::parseToken($token->toString());
        $backendConfig = JwtAuth::getConfig('backend');
        $jti = $parseToken->claims()->get('jti');
        $getExpiryDateTime = JwtAuth::getTokenExpirationTime($token->toString());
        // var_dump('$getExpiryDateTime', $getExpiryDateTime, $blacklist_enabled, $backendConfig->getLoginType());
        if ($blacklist_enabled && $backendConfig->getLoginType() == 'sso') {
            $blacklist_prefix = $options['blacklist_prefix'] ?? 'webman';
            $cacheKey = "{$blacklist_prefix}:" . $jti;
            Redis::setEx($cacheKey, $getExpiryDateTime, serialize(['valid_until' => time()]));
        }
    }

    public function verify(Token $token)
    {
        // TODO: Implement verify() method.
    }

}
