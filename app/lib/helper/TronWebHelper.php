<?php

namespace app\lib\helper;

use GuzzleHttp\Client;
use support\Log;

/**
 * TRON区块链交互助手
 * 封装TRON API调用
 */
class TronWebHelper
{
    protected Client $httpClient;
    protected string $apiUrl;
    protected ?string $apiKey;

    /**
     * @param string $apiUrl TRON API URL (如 https://api.trongrid.io)
     * @param string|null $apiKey API密钥（可选）
     */
    public function __construct(string $apiUrl = 'https://api.trongrid.io', ?string $apiKey = null)
    {
        $this->apiUrl = rtrim($apiUrl, '/');
        $this->apiKey = $apiKey;

        $headers = ['Content-Type' => 'application/json'];
        if ($apiKey) {
            $headers['TRON-PRO-API-KEY'] = $apiKey;
        }

        $this->httpClient = new Client([
            'timeout' => 30,
            'headers' => $headers,
        ]);
    }

    /**
     * 获取账户信息
     * @param string $address TRON地址
     * @return array|null
     */
    public function getAccount(string $address): ?array
    {
        try {
            $response = $this->httpClient->post($this->apiUrl . '/wallet/getaccount', [
                'json' => [
                    'address' => $address,
                    'visible' => true,
                ],
            ]);

            $data = json_decode($response->getBody()->getContents(), true);
            return $data;
        } catch (\Throwable $e) {
            Log::error("获取账户信息失败: " . $e->getMessage(), [
                'address' => $address,
            ]);
            return null;
        }
    }

    /**
     * 获取账户余额（TRX，单位：SUN，1 TRX = 1,000,000 SUN）
     * @param string $address TRON地址
     * @return int 余额（SUN）
     */
    public function getBalance(string $address): int
    {
        $account = $this->getAccount($address);
        return $account['balance'] ?? 0;
    }

    /**
     * 获取交易信息
     * @param string $txHash 交易哈希
     * @return array|null
     */
    public function getTransaction(string $txHash): ?array
    {
        try {
            $response = $this->httpClient->post($this->apiUrl . '/wallet/gettransactionbyid', [
                'json' => [
                    'value' => $txHash,
                ],
            ]);

            $data = json_decode($response->getBody()->getContents(), true);
            return $data;
        } catch (\Throwable $e) {
            Log::error("获取交易信息失败: " . $e->getMessage(), [
                'tx_hash' => $txHash,
            ]);
            return null;
        }
    }

    /**
     * 获取交易信息（通过TronGrid API）
     * @param string $txHash 交易哈希
     * @return array|null
     */
    public function getTransactionInfo(string $txHash): ?array
    {
        try {
            $response = $this->httpClient->post($this->apiUrl . '/wallet/gettransactioninfobyid', [
                'json' => [
                    'value' => $txHash,
                ],
            ]);

            $data = json_decode($response->getBody()->getContents(), true);
            return $data;
        } catch (\Throwable $e) {
            Log::error("获取交易信息失败: " . $e->getMessage(), [
                'tx_hash' => $txHash,
            ]);
            return null;
        }
    }

    /**
     * 获取账户交易列表
     * @param string $address TRON地址
     * @param int $limit 返回数量限制
     * @param string|null $fingerprint 分页指纹
     * @return array
     */
    public function getAccountTransactions(string $address, int $limit = 20, ?string $fingerprint = null): array
    {
        try {
            $params = [
                'only_confirmed' => true,
                'only_to' => false,
                'limit' => $limit,
            ];

            if ($fingerprint) {
                $params['fingerprint'] = $fingerprint;
            }

            $queryString = http_build_query($params);
            $url = $this->apiUrl . "/v1/accounts/{$address}/transactions?{$queryString}";

            $response = $this->httpClient->get($url);
            $data = json_decode($response->getBody()->getContents(), true);

            return $data['data'] ?? [];
        } catch (\Throwable $e) {
            Log::error("获取账户交易列表失败: " . $e->getMessage(), [
                'address' => $address,
            ]);
            return [];
        }
    }

    /**
     * 获取入账交易（转入指定地址的交易）
     * @param string $address TRON地址
     * @param int $minBlockHeight 最小区块高度（用于增量查询）
     * @param int $limit 返回数量限制
     * @return array
     */
    public function getIncomingTransactions(string $address, int $minBlockHeight = 0, int $limit = 50): array
    {
        try {
            $params = [
                'only_confirmed' => true,
                'only_to' => true, // 只获取转入交易
                'limit' => $limit,
                'min_block_timestamp' => 0,
            ];

            $queryString = http_build_query($params);
            $url = $this->apiUrl . "/v1/accounts/{$address}/transactions?{$queryString}";

            $response = $this->httpClient->get($url);
            $data = json_decode($response->getBody()->getContents(), true);

            $transactions = $data['data'] ?? [];

            // 过滤：只保留区块高度大于指定值的交易
            if ($minBlockHeight > 0) {
                $transactions = array_filter($transactions, function ($tx) use ($minBlockHeight) {
                    return ($tx['block_number'] ?? 0) > $minBlockHeight;
                });
            }

            // 解析交易数据
            return array_map(function ($tx) {
                return $this->parseTransaction($tx);
            }, $transactions);

        } catch (\Throwable $e) {
            Log::error("获取入账交易失败: " . $e->getMessage(), [
                'address' => $address,
            ]);
            return [];
        }
    }

    /**
     * 解析交易数据
     * @param array $tx 原始交易数据
     * @return array 解析后的交易数据
     */
    protected function parseTransaction(array $tx): array
    {
        $txInfo = $tx['raw_data'] ?? [];
        $contract = $txInfo['contract'][0] ?? [];
        $value = $contract['parameter']['value'] ?? [];

        return [
            'tx_hash' => $tx['txID'] ?? '',
            'block_number' => $tx['block_number'] ?? 0,
            'block_timestamp' => $tx['block_timestamp'] ?? 0,
            'from_address' => $value['owner_address'] ?? '',
            'to_address' => $value['to_address'] ?? '',
            'amount' => ($value['amount'] ?? 0) / 1000000, // SUN转TRX
            'result' => $tx['ret'][0]['contractRet'] ?? 'UNKNOWN',
            'contract_type' => $contract['type'] ?? '',
        ];
    }

    /**
     * 验证TRON地址格式
     * @param string $address TRON地址
     * @return bool
     */
    public static function isValidAddress(string $address): bool
    {
        // TRON地址通常以T开头，长度为34个字符
        return preg_match('/^T[a-zA-Z0-9]{33}$/', $address) === 1;
    }

    /**
     * 发送TRX转账（需要私钥）
     * @param string $fromAddress 发送地址
     * @param string $toAddress 接收地址
     * @param int $amount 金额（SUN）
     * @param string $privateKey 私钥
     * @return array|null
     */
    public function sendTransaction(string $fromAddress, string $toAddress, int $amount, string $privateKey): ?array
    {
        // TODO: 实现交易签名和广播
        // 这需要使用TRON私钥签名库（如 kornrunner/keccak 或 tron-api/tron-php）
        // 当前仅作为占位符，实际使用时需要完善
        Log::warning("sendTransaction方法需要完善实现");
        return null;
    }

    /**
     * 转换SUN到TRX
     * @param int $sun SUN数量
     * @return float TRX数量
     */
    public static function sunToTrx(int $sun): float
    {
        return $sun / 1000000;
    }

    /**
     * 转换TRX到SUN
     * @param float $trx TRX数量
     * @return int SUN数量
     */
    public static function trxToSun(float $trx): int
    {
        return (int)($trx * 1000000);
    }
}
