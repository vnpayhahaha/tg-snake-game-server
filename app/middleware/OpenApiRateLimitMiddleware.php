<?php

namespace app\middleware;

use app\model\ModelTenantApp;
use app\service\TenantApiInterfaceService;
use DI\Attribute\Inject;
use support\Cache;
use support\Context;
use Webman\Http\Request;
use Webman\Http\Response;
use Webman\MiddlewareInterface;
use Webman\RateLimiter\Limiter;

class OpenApiRateLimitMiddleware implements MiddlewareInterface
{
    #[Inject]
    protected TenantApiInterfaceService $tenantApiInterfaceService;

    public function process(Request $request, callable $handler): Response
    {
        // /v1/collection/orders
        $path = $request->path();
        $rate_limit = $this->tenantApiInterfaceService->getRateLimitByApiUri($path);
        $app = Context::get(ModelTenantApp::class);
        $tenant_id = $app->tenant_id;
        $key = $tenant_id . $path;
        var_dump('---key--limt-=-', $key, $rate_limit);
        $message = 'Too Many Requests, please try again later.(' . $rate_limit . ' times per second for ' . $path . ')';
        Limiter::check($key, $rate_limit, 1, $message);
        // 相同参数md5 hash缓存60s, 相同参数的请求只计算一次
        $params = $request->all();
        $md5 = md5(json_encode($params));
        $cacheKey = $tenant_id . $path . $md5;
        if (Cache::has($cacheKey)) {
            return new Response(429, [], 'Please do not request frequently');
        }
        Cache::set($cacheKey, 1, 60);

        return $handler($request);
    }
}