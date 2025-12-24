<?php

namespace app\middleware;



use Ramsey\Uuid\Uuid;

use Webman\Http\Request;

use Webman\Http\Response;
use Webman\MiddlewareInterface;

class RequestIdMiddleware implements MiddlewareInterface
{
    public function process(Request $request, callable $handler): Response
    {
        // 生成唯一请求ID（可以使用UUID或更简单的方案）
        $requestId = $request->header('X-Request-Id') ?? (Uuid::uuid4())->toString();

        // 将请求ID存储到请求对象中
        $request->requestId = $requestId;

        // 可选：将请求ID添加到响应头
        return $handler($request)->withHeader('X-Request-Id', $requestId);
    }
}
