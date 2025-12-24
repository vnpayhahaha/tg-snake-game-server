<?php

namespace http\common\middleware;

use app\exception\BusinessException;
use app\exception\UnprocessableEntityException;
use app\lib\enum\ResultCode;
use app\model\ModelChannel;
use app\repository\ChannelCallbackRecordRepository;
use app\service\ChannelService;
use Carbon\Carbon;
use DI\Attribute\Inject;
use support\Log;
use Throwable;
use Webman\Http\Request;
use Webman\Http\Response;
use Webman\MiddlewareInterface;

class ChannelCallbackRecordMiddleware implements MiddlewareInterface
{
    #[Inject]
    protected ChannelService $channelService;

    #[Inject]
    protected ChannelCallbackRecordRepository $callbackRecordRepository;

    public function process(Request $request, callable $handler): Response
    {
        $startTime = microtime(true);
        $callbackId = $this->generateCallbackId();

        // 从路由参数中提取channel_code
        $channelCode = $request->route?->param('channel_code') ?? '';
        $callbackType = $this->determineCallbackType($request->path());

        Log::info("回调中间件 - {$callbackType}订单回调开始", [
            'callback_id'  => $callbackId,
            'channel_code' => $channelCode,
            'params'       => $request->all(),
            'url'          => $request->fullUrl()
        ]);

        // 获取通道信息
        $modelChannel = $this->getChannelByCode($channelCode);
        if (!$modelChannel) {
            Log::warning("回调中间件 - 无效的渠道代码", [
                'callback_id'  => $callbackId,
                'channel_code' => $channelCode,
                'path'         => $request->path()
            ]);
            throw new UnprocessableEntityException(ResultCode::INVALID_CHANNEL);
        }

        // 记录回调开始
        $callbackRecord = $this->recordCallbackStart($request, $modelChannel, $callbackId, $callbackType);
        if (!$callbackRecord) {
            Log::error("回调中间件 - {$callbackType}订单回调异常", [
                'callback_id'  => $callbackId,
                'channel_code' => $channelCode,
                'error'        => '回调记录创建失败',
                'elapsed_time' => (microtime(true) - $startTime) * 1000
            ]);
            throw new BusinessException(ResultCode::UNKNOWN, '回调记录创建失败');
        }
        // 将回调记录信息添加到请求属性中，供控制器使用
        $request->callback_record = $callbackRecord;
        $request->callback_start_time = $startTime;

        try {
            // 执行后续处理
            $response = $handler($request);

            // 记录成功响应
            $this->updateCallbackRecord(
                $callbackRecord->id,
                true,
                '回调处理完成',
                microtime(true) - $startTime,
                $response
            );

            Log::info("回调中间件 - {$callbackType}订单回调完成", [
                'callback_id'  => $callbackId,
                'elapsed_time' => (microtime(true) - $startTime) * 1000,
                'status'       => 'success'
            ]);

            return $response;

        } catch (Throwable $e) {
            // 记录异常，并尝试提取异常信息作为响应内容
            $exceptionResponse = $this->createExceptionResponse($e);
            $this->updateCallbackRecord(
                $callbackRecord->id,
                false,
                '回调处理异常: ' . $e->getMessage(),
                microtime(true) - $startTime,
                $exceptionResponse
            );

            Log::error("回调中间件 - {$callbackType}订单回调异常", [
                'callback_id'  => $callbackId,
                'channel_code' => $channelCode,
                'error'        => $e->getMessage(),
                'trace'        => $e->getTraceAsString(),
                'elapsed_time' => (microtime(true) - $startTime) * 1000
            ]);

            throw $e;
        }
    }

    /**
     * 生成回调ID
     */
    private function generateCallbackId(): string
    {
        return uniqid('callback_', true) . '_' . time();
    }

    /**
     * 判断回调类型
     */
    private function determineCallbackType(string $path): string
    {
        if (str_contains($path, '/collection/')) {
            return 'collection';
        } elseif (str_contains($path, '/disbursement/')) {
            return 'disbursement';
        }
        return 'unknown';
    }

    /**
     * 根据渠道代码获取通道信息
     */
    private function getChannelByCode(string $channelCode): ?ModelChannel
    {
        if (empty($channelCode)) {
            return null;
        }

        return $this->channelService->repository->getQuery()
            ->where('channel_code', $channelCode)
            ->first();
    }

    /**
     * 记录回调开始
     */
    private function recordCallbackStart(Request $request, ModelChannel $modelChannel, string $callbackId, string $callbackType): object
    {
        // 尝试从请求头中获取 request-id
        $originalRequestId = $this->extractRequestId($request);

        return $this->callbackRecordRepository->create([
            'callback_id'          => $callbackId,
            'channel_id'           => $modelChannel->id,
            'original_request_id'  => $originalRequestId,
            'callback_type'        => $callbackType,
            'callback_url'         => $request->fullUrl(),
            'callback_http_method' => $request->method(),
            'callback_params'      => json_encode($request->all(), JSON_UNESCAPED_UNICODE),
            'callback_headers'     => json_encode($request->header(), JSON_UNESCAPED_UNICODE),
            'callback_body'        => $request->rawBody(),
            'callback_time'        => Carbon::now(),
            'client_ip'            => $request->getRealIp(),
            'status'               => 0, // 0-未验签
            'response_content'     => '',
            'process_result'       => '处理中...',
            'elapsed_time'         => 0,
        ]);
    }

    /**
     * 更新回调记录
     */
    private function updateCallbackRecord(int $callbackRecordId, bool $success, string $processResult, float $elapsedTime, ?Response $response): void
    {
        try {
            $responseContent = $this->extractResponseContent($response);

            $this->callbackRecordRepository->updateById($callbackRecordId, [
                'status'           => $success ? 1 : 2, // 1-验签成功, 2-验签失败
                'process_result'   => $processResult,
                'elapsed_time'     => (int)($elapsedTime * 1000), // 转换为毫秒
                'response_content' => $responseContent,
            ]);
        } catch (Throwable $e) {
            Log::error('中间件更新回调记录失败', [
                'callback_record_id' => $callbackRecordId,
                'error'              => $e->getMessage()
            ]);
        }
    }

    /**
     * 提取响应内容
     */
    private function extractResponseContent(?Response $response): string
    {
        if (!$response) {
            return 'no_response';
        }

        try {
            $responseData = $response->rawBody();
            if (!empty($responseData)) {
                return is_string($responseData) ? $responseData : json_encode($responseData, JSON_UNESCAPED_UNICODE);
            }

            // 如果rawBody为空，尝试构建响应信息
            $statusCode = method_exists($response, 'getStatusCode') ? $response->getStatusCode() : 200;
            $headers = method_exists($response, 'getHeaders') ? $response->getHeaders() : [];

            return json_encode([
                'status_code' => $statusCode,
                'headers'     => $headers,
                'timestamp'   => date('Y-m-d H:i:s')
            ], JSON_UNESCAPED_UNICODE) ?: 'response_info_failed';

        } catch (Throwable $e) {
            return 'response_extraction_failed: ' . $e->getMessage();
        }
    }

    /**
     * 从请求头中提取 request-id
     */
    private function extractRequestId(Request $request): string
    {
        // 尝试多种可能的请求ID头字段名称
        $possibleHeaders = [
            'request-id',
            'Request-ID',
            'x-request-id',
            'X-Request-ID',
            'X-Request-Id',
            'trace-id',
            'X-Trace-ID',
            'correlation-id',
            'X-Correlation-ID'
        ];

        foreach ($possibleHeaders as $header) {
            $requestId = $request->header($header);
            if (!empty($requestId)) {
                return is_array($requestId) ? $requestId[0] : $requestId;
            }
        }

        // 如果没有找到，返回空字符串
        return '';
    }

    /**
     * 创建异常响应对象（用于记录异常信息）
     */
    private function createExceptionResponse(Throwable $e): ?Response
    {
        try {
            // 创建一个包含异常信息的响应内容
            $exceptionData = [
                'error'          => true,
                'exception_type' => get_class($e),
                'message'        => $e->getMessage(),
                'code'           => $e->getCode(),
                'file'           => $e->getFile(),
                'line'           => $e->getLine(),
                'timestamp'      => date('Y-m-d H:i:s'),
            ];

            // 如果是业务异常，尝试获取更多上下文信息
            if (method_exists($e, 'getResultCode')) {
                $exceptionData['result_code'] = $e->getResultCode();
            }

            $exceptionJson = json_encode($exceptionData, JSON_UNESCAPED_UNICODE);

            // 创建一个临时响应对象来存储异常信息
            $response = new Response(500, [], $exceptionJson);
            return $response;

        } catch (Throwable $createException) {
            // 如果创建异常响应失败，返回null，让extractResponseContent处理
            return null;
        }
    }
}