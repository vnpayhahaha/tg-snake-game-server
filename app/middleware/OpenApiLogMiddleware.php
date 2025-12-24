<?php

namespace app\middleware;

use app\event\Dto\TenantAppLogEventDto;
use app\model\ModelTenantApp;
use support\Context;
use Webman\Http\Request;
use Webman\Http\Response;
use Webman\MiddlewareInterface;
use Webman\Event\Event;

class OpenApiLogMiddleware implements MiddlewareInterface
{

    public function process(Request $request, callable $handler): Response
    {
        // 记录请求开始时间
        $startTime = microtime(true);
        $tenantApp = Context::get(ModelTenantApp::class);
        $response = $handler($request);
        // 计算请求耗时（毫秒）
        $duration = round((microtime(true) - $startTime) * 1000);
        // 将耗时信息添加到响应头中（可选）
        $response->header('X-Request-Duration', $duration . 'ms');
        $responseBody = $response->rawBody();
        // 判断$responseBody是不是json
        $resultBody = json_decode($responseBody, true);
        var_dump('$resultBody==');
        Event::dispatch('tenant.app.log', new TenantAppLogEventDto([
            'tenant_id'        => $tenantApp->tenant_id,
            'app_id'           => $tenantApp->id,
            'app_key'          => $tenantApp->app_key,
            'access_path'      => $request->path(),
            'request_id'       => $request->requestId,
            'request_data'     => json_encode($request->all()),
            'response_code'    => $resultBody['code'] ?? 0,
            'response_success' => $resultBody['success'] ?? false,
            'response_message' => $resultBody['message'] ?? '',
            'response_data'    => $responseBody,
            'ip'               => $request->getRealIp(false),
            'duration'         => $duration,
            'access_time'      => date('Y-m-d H:i:s'),
        ]));
        return $response;
    }

}
