<?php

namespace app\upstream\aipay;

use app\upstream\Handle\Basic;
use GuzzleHttp\Exception\GuzzleException;
use JsonException;
use support\Log;
use Throwable;

class Base extends Basic
{
    protected string $service_name = 'aipay';
    protected string $url = 'https://top.adkjk.in/aipay-api';

    protected string $secret_key = 'abc#123!';
    protected int $merchant_id = 999;
    protected string $return_url = 'https://www.google.com';

    /**
     * @throws Throwable
     * @throws GuzzleException
     * @throws JsonException
     */
    public function post(string $uri, array $data): mixed
    {
        var_dump($this->service_name . '===post==',$this->url, $uri, $data);
        try {
            $result = $this->_post($this->url, $uri, $data, [
                'Content-Type' => 'application/json;charset=utf-8',
            ]);
            if ($result['status_code'] === 200 || $result['status_code'] === 202) {
                return json_decode($result['result'], true, 512, JSON_THROW_ON_ERROR);
            }
            throw new \RuntimeException($this->service_name . ' 接口异常:' . $result['result']);
        } catch (Throwable $e) {
            $errMsg = $e->getMessage();
            Log::error("{$this->service_name} post 请求异常[{$this->url}]：" . $errMsg, [$e]);
            throw $e;
        }
    }
}
