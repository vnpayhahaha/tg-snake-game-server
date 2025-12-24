<?php

namespace app\upstream\Handle;

use app\model\ModelChannelAccount;
use app\repository\ChannelRequestRecordRepository;
use Carbon\Carbon;
use DI\Attribute\Inject;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use JetBrains\PhpStorm\ArrayShape;
use JsonException;
use support\Log;
use Throwable;

class Basic
{
    #[Inject]
    protected ChannelRequestRecordRepository $requestRecordRepository;

    protected ModelChannelAccount $channel_account;
    protected int $timeout = 30;

    protected string $collection_notify_url;
    protected string $payment_notify_url;

    /**
     * 发起 POST 请求并记录日志
     * @param string $host
     * @param string $path
     * @param array $data
     * @param array $headers
     * @return array
     * @throws GuzzleException
     * @throws JsonException
     * @throws Throwable
     */
    #[ArrayShape([
        'status_code' => 'int',
        'result'      => 'string',
    ])]
    protected function _post(string $host, string $path, array $data, array $headers = []): array
    {
        $requestId = $this->generateRequestId();
        $requestUrl = $host . $path;
        $requestTime = Carbon::now();

        $defaultHeaders = [
            'Content-Type' => 'application/json;charset=utf-8',
            'User-Agent'   => 'Upstream-Client/1.0',
        ];

        $headers = array_merge($defaultHeaders, $headers);

        try {
            $client = new Client();
            $body = json_encode($data, JSON_THROW_ON_ERROR);

            $options = [
                'timeout' => $this->timeout,
                'headers' => $headers,
                'body'    => $body,
            ];

            // 记录请求开始时间
            $startTime = microtime(true);

            // 记录请求日志
            $this->logRequest($requestId, $path, $requestUrl, 'POST', $data, $headers, $body, $requestTime);

            Log::info("POST 请求", [
                'request_id' => $requestId,
                'url'        => $requestUrl,
                'method'     => 'POST',
                'data'       => $data
            ]);

            $response = $client->request('POST', $requestUrl, $options);

            // 计算耗时
            $elapsedTime = (int)((microtime(true) - $startTime) * 1000);
            $responseTime = Carbon::now();
            $statusCode = $response->getStatusCode();
            $responseHeaders = $response->getHeaders();
            $responseBody = $response->getBody()->getContents();

            // 记录响应日志
            $this->logResponse($requestId, $statusCode, '', $responseHeaders, $responseBody, '', $responseTime, $elapsedTime);

            Log::info("POST 响应", [
                'request_id'    => $requestId,
                'status_code'   => $statusCode,
                'elapsed_time'  => $elapsedTime . 'ms',
                'response_body' => $responseBody
            ]);

            return [
                'status_code' => $statusCode,
                'result'      => $responseBody
            ];

        } catch (Throwable $e) {
            $elapsedTime = (int)((microtime(true) - ($startTime ?? microtime(true))) * 1000);
            $errorMessage = $e->getMessage();

            // 记录错误日志
            $this->logError($requestId, $errorMessage, Carbon::now(), $elapsedTime);

            Log::error("POST 请求异常", [
                'request_id'   => $requestId,
                'url'          => $requestUrl,
                'error'        => $errorMessage,
                'elapsed_time' => $elapsedTime . 'ms',
                'exception'    => $e
            ]);

            throw $e;
        }
    }

    /**
     * 发起 GET 请求并记录日志
     *
     * @param string $host
     * @param string $path
     * @param array $params 查询参数
     * @param array $headers 请求头
     * @return mixed 响应数据
     * @throws GuzzleException
     * @throws Throwable
     * @throws JsonException
     */
    #[ArrayShape([
        'status_code' => 'int',
        'result'      => 'string',
    ])]
    protected function _get(string $host, string $path, array $params = [], array $headers = []): array
    {
        $requestId = $this->generateRequestId();
        $requestUrl = $host . $path . (!empty($params) ? '?' . http_build_query($params) : '');
        $requestTime = Carbon::now();

        $defaultHeaders = [
            'User-Agent' => 'Upstream-Client/1.0',
        ];

        $headers = array_merge($defaultHeaders, $headers);

        try {
            $client = new Client();

            $options = [
                'timeout' => $this->timeout,
                'headers' => $headers,
            ];

            // 记录请求开始时间
            $startTime = microtime(true);

            // 记录请求日志
            $this->logRequest($requestId, $path, $requestUrl, 'GET', $params, $headers, '', $requestTime);

            Log::info("发起 GET 请求", [
                'request_id' => $requestId,
                'url'        => $requestUrl,
                'method'     => 'GET',
                'params'     => $params
            ]);

            $response = $client->request('GET', $requestUrl, $options);

            // 计算耗时
            $elapsedTime = (int)((microtime(true) - $startTime) * 1000);
            $responseTime = Carbon::now();
            $statusCode = $response->getStatusCode();
            $responseHeaders = $response->getHeaders();
            $responseBody = $response->getBody()->getContents();

            // 记录响应日志
            $this->logResponse($requestId, $statusCode, '', $responseHeaders, $responseBody, '', $responseTime, $elapsedTime);

            Log::info("GET 响应", [
                'request_id'    => $requestId,
                'status_code'   => $statusCode,
                'elapsed_time'  => $elapsedTime . 'ms',
                'response_body' => $responseBody
            ]);

            return [
                'status_code' => $statusCode,
                'result'      => $responseBody
            ];

        } catch (Throwable $e) {
            $elapsedTime = (int)((microtime(true) - ($startTime ?? microtime(true))) * 1000);
            $errorMessage = $e->getMessage();

            // 记录错误日志
            $this->logError($requestId, $errorMessage, Carbon::now(), $elapsedTime);

            Log::error("GET 请求异常", [
                'request_id'   => $requestId,
                'url'          => $requestUrl,
                'error'        => $errorMessage,
                'elapsed_time' => $elapsedTime . 'ms',
                'exception'    => $e
            ]);

            throw $e;
        }
    }

    /**
     * 生成请求ID
     *
     * @return string
     */
    protected function generateRequestId(): string
    {
        return uniqid() . '_' . time();
    }

    /**
     * 记录请求日志
     *
     * @param string $requestId 请求ID
     * @param string $apiMethod API方法
     * @param string $requestUrl 请求URL
     * @param string $httpMethod HTTP方法
     * @param array $requestParams 请求参数
     * @param array $requestHeaders 请求头
     * @param string $requestBody 请求体
     * @param Carbon $requestTime 请求时间
     * @return void
     * @throws Throwable
     */
    protected function logRequest(string $requestId, string $apiMethod, string $requestUrl, string $httpMethod, array $requestParams, array $requestHeaders, string $requestBody, Carbon $requestTime): void
    {
        try {
            $this->requestRecordRepository->create([
                'request_id'      => $requestId,
                'channel_id'      => $this->channel_account->channel_id,
                'api_method'      => $apiMethod ?: parse_url($requestUrl, PHP_URL_PATH),
                'request_url'     => $requestUrl,
                'http_method'     => $httpMethod,
                'request_params'  => json_encode($requestParams, JSON_UNESCAPED_UNICODE),
                'request_headers' => json_encode($requestHeaders, JSON_UNESCAPED_UNICODE),
                'request_body'    => $requestBody,
                'request_time'    => $requestTime,
            ]);
        } catch (Throwable $e) {
            Log::error("记录请求日志失败", [
                'request_id' => $requestId,
                'error'      => $e->getMessage()
            ]);
        }
    }

    /**
     * 记录响应日志
     *
     * @param string $requestId 请求ID
     * @param int $httpStatusCode HTTP状态码
     * @param string $responseStatus 业务响应状态
     * @param array $responseHeaders 响应头
     * @param string $responseBody 响应体
     * @param string $errorMessage 错误信息
     * @param Carbon $responseTime 响应时间
     * @param int $elapsedTime 耗时(毫秒)
     * @return void
     */
    protected function logResponse(string $requestId, int $httpStatusCode, string $responseStatus, array $responseHeaders, string $responseBody, string $errorMessage, Carbon $responseTime, int $elapsedTime): void
    {
        try {
            $record = $this->requestRecordRepository->findByFilter(['request_id' => $requestId]);
            if ($record) {
                $this->requestRecordRepository->updateById($record->id, [
                    'http_status_code' => $httpStatusCode,
                    'response_status'  => $responseStatus,
                    'response_headers' => json_encode($responseHeaders, JSON_UNESCAPED_UNICODE),
                    'response_body'    => $responseBody,
                    'error_message'    => $errorMessage,
                    'response_time'    => $responseTime,
                    'elapsed_time'     => $elapsedTime,
                ]);
            }
        } catch (Throwable $e) {
            Log::error("更新响应日志失败", [
                'request_id' => $requestId,
                'error'      => $e->getMessage()
            ]);
        }
    }

    /**
     * 记录错误日志
     *
     * @param string $requestId 请求ID
     * @param string $errorMessage 错误信息
     * @param Carbon $responseTime 响应时间
     * @param int $elapsedTime 耗时(毫秒)
     * @return void
     */
    protected function logError(string $requestId, string $errorMessage, Carbon $responseTime, int $elapsedTime): void
    {
        try {
            $record = $this->requestRecordRepository->findByFilter(['request_id' => $requestId]);
            if ($record) {
                $this->requestRecordRepository->updateById($record->id, [
                    'error_message' => $errorMessage,
                    'response_time' => $responseTime,
                    'elapsed_time'  => $elapsedTime,
                ]);
            }
        } catch (Throwable $e) {
            Log::error("更新错误日志失败", [
                'request_id' => $requestId,
                'error'      => $e->getMessage()
            ]);
        }
    }

    /**
     * 设置超时时间
     *
     * @param int $timeout
     * @return $this
     */
    public function setTimeout(int $timeout): static
    {
        $this->timeout = $timeout;
        return $this;
    }
}