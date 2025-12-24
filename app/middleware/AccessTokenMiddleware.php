<?php

namespace app\middleware;

use app\lib\annotation\NoNeedLogin;
use app\lib\JwtAuth\exception\JwtException;
use app\lib\JwtAuth\facade\JwtAuth;
use app\lib\JwtAuth\handle\RequestToken;
use support\Context;
use support\Log;
use Webman\Http\Request;
use Webman\Http\Response;
use Webman\MiddlewareInterface;

class AccessTokenMiddleware implements MiddlewareInterface
{

    public function process(Request $request, callable $handler): Response
    {
        if ($request->method() === 'OPTIONS') {
            response('', 204);
        }
        $controllerClass = $request->controller;
        $action = $request->action;
        if ($controllerClass && $action) {
            try {
                $reflectionClass = new \ReflectionClass($controllerClass);
                // 检查方法级别的注解
                if ($reflectionClass->hasMethod($action)) {
                    $reflectionMethod = $reflectionClass->getMethod($action);
                    $methodAnnotation = $reflectionMethod->getAttributes(NoNeedLogin::class);
                    if (!empty($methodAnnotation)) {
                        $request->noNeedLogin = true;
                        return $handler($request);
                    }
                }
            } catch (\Throwable $e) {
                // 记录错误日志但不中断请求
                Log::error('Failed to process AccessTokenMiddleware: ' . $e->getMessage());
            }
        }
        if ($route = $request->route) {
            $store = $route->param('store');
        }
        $store = $store ?? (\request()->app === '' ? 'default' : \request()->app);
        try {
            $requestToken = new RequestToken();
            $tokenConf = JwtAuth::getConfig($store);
            $handel = $tokenConf->getType();
            $token = $requestToken->get($handel);
            //var_dump('token ==', $token);

            JwtAuth::verify($token);
            $verifyToken = JwtAuth::getVerifyToken();
            if ($token !== $verifyToken->toString()) {
                Context::set('token', $verifyToken->toString());
            } else {
                Context::set('token', $token);
            }
            //var_dump('refresh_token == autoRefresh ==');
            $request->user = JwtAuth::getUser();
            Context::set('user', $request->user);
            $response = $handler($request);
            if ($token !== $verifyToken->toString()) {
                return $response->withHeaders([
                    'Access-Control-Expose-Headers'     => 'Automatic-Renewal-Token,Automatic-Renewal-Token-ExpireAt,Automatic-Renewal-Token-RefreshAt',
                    'Automatic-Renewal-Token'           => $verifyToken->toString(),
                    'Automatic-Renewal-Token-ExpireAt'  => $tokenConf->getExpires(),
                    'Automatic-Renewal-Token-RefreshAt' => $tokenConf->getRefreshTTL(),
                ]);
            }
            return $response;
        } catch (JwtException $e) {
            throw new JwtException($e->getMessage(), $e->getCode());
        }
    }

}
