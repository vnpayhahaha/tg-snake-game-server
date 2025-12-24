<?php

namespace app\middleware;

use app\exception\BusinessException;
use app\lib\annotation\Permission;
use app\lib\enum\ResultCode;
use app\lib\JwtAuth\exception\JwtException;
use app\model\ModelUser;
use Webman\Http\Request;
use Webman\Http\Response;
use Webman\MiddlewareInterface;

class PermissionMiddleware implements MiddlewareInterface
{
    public function process(Request $request, callable $handler): Response
    {
        if ($request->noNeedLogin) {
            return $handler($request);
        }
        $user = $request->user;
        if (!$user) {
            throw new JwtException(trans('unauthorized', [], 'jwt'));
        }
        // 判断用户状态
        if ($user->status->isDisable()) {
            throw new JwtException(trans('disable', [], 'user'));
        }
        if ($user->isSuperAdmin()) {
            return $handler($request);
        }
        $controllerClass = $request->controller;
        $action = $request->action;
        if ($controllerClass && $action) {
            $reflectionClass = new \ReflectionClass($controllerClass);
            // 检查方法级别的注解
            if ($reflectionClass->hasMethod($action)) {
                $reflectionMethod = $reflectionClass->getMethod($action);
                $attributes = $reflectionMethod->getAttributes(Permission::class);
                if(!$attributes){
                    return $handler($request);
                }
                if ($methodAnnotation = $attributes[0]->newInstance()) {
                    // 获取注解参数
                    $this->handlePermission($methodAnnotation, $user);
                }
            }
        }

        return $handler($request);
    }

    private function handlePermission(Permission $permission, ModelUser $user): void
    {
        $operation = $permission->getOperation();
        $codes = $permission->getCode();
        foreach ($codes as $code) {
            $isMenu = $user->hasPermission($code);
            if ($operation === Permission::OPERATION_AND && !$isMenu) {
                throw new BusinessException(code: ResultCode::FORBIDDEN);
            }
            if ($operation === Permission::OPERATION_OR && $isMenu) {
                return;
            }
        }
        if ($operation === Permission::OPERATION_OR) {
            throw new BusinessException(code: ResultCode::FORBIDDEN);
        }
    }

}
