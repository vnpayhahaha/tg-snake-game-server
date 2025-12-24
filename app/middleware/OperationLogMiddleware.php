<?php

namespace app\middleware;

use app\event\Dto\OperationEventDto;
use app\lib\annotation\OperationLog;
use app\lib\JwtAuth\facade\JwtAuth;
use support\Context;
use support\Log;
use Webman\Event\Event;
use Webman\Http\Request;
use Webman\Http\Response;
use Webman\MiddlewareInterface;

class OperationLogMiddleware implements MiddlewareInterface
{
    public function process(Request $request, callable $handler): Response
    {
        // 记录请求开始时间
        $startTime = microtime(true);
        $response = $handler($request);
        // 计算请求耗时（毫秒）
        $duration = round((microtime(true) - $startTime) * 1000);
        // 将耗时信息添加到响应头中（可选）
        $response->header('X-Request-Duration', $duration . 'ms');
        $controllerClass = $request->controller;
        $action = $request->action;
        // 判断请求不是post put delete
        $methodPass = in_array($request->method(), ['POST', 'PUT', 'DELETE']);
        if (!$methodPass) {
            Log::info(date('Y-m-d H:i:s') . " RequestId:{$request->requestId} [$duration ms]" . ' 请求方法:[' . $request->method() . '] 请求路由:' . $request->path() . ' 请求参数:' . json_encode($request->all()));
            return $response;
        }

        if ($controllerClass && $action) {
            try {
                $reflectionClass = new \ReflectionClass($controllerClass);
                // 检查方法级别的注解
                if ($reflectionClass->hasMethod($action)) {
                    $reflectionMethod = $reflectionClass->getMethod($action);

                    if ($methodAnnotation = $reflectionMethod->getAttributes(OperationLog::class)[0] ?? null) {
                        // 获取注解参数
                        $isDownload = false;
                        if (!empty($response->getHeader('content-description')) && !empty($response->getHeader('content-transfer-encoding'))) {
                            $isDownload = true;
                        }
                        Event::dispatch('operation.log', new OperationEventDto($this->getRequestInfo($request, [
                            'name'             => $methodAnnotation->getArguments()[0] ?? $request->method(),
                            'request_duration' => $duration,
                            'response_code'    => $response->getStatusCode(),
                            'response_data'    => $isDownload ? '文件下载' : $response->rawBody(),
                        ])));
                    }
                }
            } catch (\Throwable $e) {
                // 记录错误日志但不中断请求
                Log::error('Failed to process operation log: ' . $e->getMessage());
            }
        }

        return $response;
    }

    protected function getRequestInfo(Request $request, array $data): array
    {

        $ip = \request()->getRealIp(false);
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            // echo "$ip 是一个有效的IPv4地址";
            $ipLocation = $ip;
        } else {
            // echo "$ip 不是一个有效的IPv4地址";
            $ipLocation = '--';
        }
        // 判断$data['response_data'] 是不是json ,转换数组
        $is_success = false;
        if ($response_data = json_decode($data['response_data'], true)) {
            $is_success = $response_data['success'] ?? false;
        }
        $operationLog = [
            'time'             => date('Y-m-d H:i:s'),
            'method'           => $request->method(),
            'router'           => $request->path(),
            'ip'               => $ip,
            'ip_location'      => $ipLocation,
            'service_name'     => $data['name'] ?? '--',
            'request_params'   => json_encode($request->all()),
            'response_status'  => $data['response_code'],
            'response_data'    => $data['response_data'],
            'is_success'       => ($data['response_code'] == 200 && $is_success) ? 1 : 2,
            'request_id'       => $request->requestId,
            'request_duration' => $data['request_duration'] ?? 0,
        ];
        try {
            $token = Context::get('token') ?? '';
            JwtAuth::parseToken($token);
            $loginUser = JwtAuth::getUser();
            $operationLog['username'] = $loginUser->username;
            $operationLog['operator_id'] = $loginUser->id;
        } catch (\Exception $e) {
            $operationLog['username'] = trans('no_login_user', [], 'jwt');
        }

        return $operationLog;
    }

}
