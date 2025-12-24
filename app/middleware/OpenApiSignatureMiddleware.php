<?php

namespace app\middleware;

use app\constants\TenantApp;
use app\lib\enum\ResultCode;
use app\model\ModelTenantApp;
use app\service\TenantAppService;
use DI\Attribute\Inject;
use support\Context;
use Webman\Http\Request;
use Webman\Http\Response;
use Webman\MiddlewareInterface;

class OpenApiSignatureMiddleware implements MiddlewareInterface
{
    #[Inject]
    protected TenantAppService $tenantAppService;

    public function process(Request $request, callable $handler): Response
    {
        // 如果header中获取 Origin 的值为dev
        if ($request->header('Origin') == 'dev') {
            $app = $this->tenantAppService->queryByAppKey($request->post('app_key', '0cb3bd11ae'));
            Context::set(ModelTenantApp::class, $app);
            return $handler($request);
        }

        $params = $request->all();
        // 获取参数sign
        $sign = $params['sign'] ?? null;
        if (!$sign || !filled($sign)) {
            return $this->errorHandler(ResultCode::OPENAPI_SIGN_IS_REQUIRED, 'sign is required');
        }
        $app_key = $params['app_key'] ?? null;
        if (!$app_key || !filled($app_key)) {
            return $this->errorHandler(ResultCode::OPENAPI_APP_KEY_IS_REQUIRED, 'app_key is required');
        }
        // 时间戳
        $timestamp = $params['timestamp'] ?? null;
        if (!$timestamp || !filled($timestamp)) {
            return $this->errorHandler(ResultCode::OPENAPI_TIMESTAMP_IS_REQUIRED, 'timestamp is required');
        }
        // 时间戳 已失效一分钟
        $timestamp = (int) $timestamp;
        if (time() - $timestamp > 60) {
            return $this->errorHandler(ResultCode::OPENAPI_TIMESTAMP_IS_EXPIRED, 'timestamp is expired');
        }
        $app = $this->tenantAppService->queryByAppKey($app_key);
        if (!$app) {
            return $this->errorHandler(ResultCode::OPENAPI_APP_KEY_IS_INVALID, 'app_key is invalid');
        }
        // 判断$app 状态
        var_dump('判断$app 状态:',$app->status);
        if ($app->status === TenantApp::STATUS_DISABLE) {
            return $this->errorHandler(ResultCode::OPENAPI_APP_IS_DISABLED, 'app is disabled');
        }
        unset($params['sign']);
        $md5_sign = md5_signature($params, $app->app_secret);
        var_dump('正确的sign:',$md5_sign);
        if ($sign !== $md5_sign) {
            return $this->errorHandler(ResultCode::OPENAPI_SIGN_IS_INVALID, 'sign is invalid');
        }
        Context::set(ModelTenantApp::class, $app);
        return $handler($request);
    }

    protected function errorHandler(ResultCode $code, string $message): \support\Response
    {
        $request = Context::get(Request::class);
        $resultData = [
            'request_id' => $request->requestId,
            'path'       => $request->path(),
            'success'    => false,
            'code'       => $code,
            'message'    => $message,
        ];
        return new \support\Response(200, ['Content-Type' => 'application/json'], json_encode($resultData));
    }
}
