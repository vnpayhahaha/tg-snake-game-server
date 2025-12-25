<?php

namespace app\lib\helper;

use support\Log;

/**
 * TRON交易签名助手
 * 使用TronGrid API创建和广播交易
 */
class TronTransactionHelper
{
    protected string $apiUrl;
    protected ?string $apiKey;

    public function __construct(string $apiUrl = 'https://api.trongrid.io', ?string $apiKey = null)
    {
        $this->apiUrl = rtrim($apiUrl, '/');
        $this->apiKey = $apiKey ?: (getenv('TRONGRID_API_KEY') ?: null);
    }

    /**
     * 创建TRX转账交易
     * @param string $fromAddress 发送地址
     * @param string $toAddress 接收地址
     * @param int $amountSun 金额（SUN）
     * @return array|null 未签名的交易
     */
    public function createTransaction(string $fromAddress, string $toAddress, int $amountSun): ?array
    {
        try {
            $headers = ['Content-Type' => 'application/json'];
            if ($this->apiKey) {
                $headers['TRON-PRO-API-KEY'] = $this->apiKey;
            }

            $client = new \GuzzleHttp\Client([
                'timeout' => 30,
                'headers' => $headers,
            ]);

            $response = $client->post($this->apiUrl . '/wallet/createtransaction', [
                'json' => [
                    'to_address' => $toAddress,
                    'owner_address' => $fromAddress,
                    'amount' => $amountSun,
                    'visible' => true,
                ],
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            if (isset($data['Error'])) {
                Log::error("创建TRON交易失败", [
                    'error' => $data['Error'],
                    'from' => $fromAddress,
                    'to' => $toAddress,
                    'amount' => $amountSun,
                ]);
                return null;
            }

            return $data;

        } catch (\Throwable $e) {
            Log::error("创建TRON交易异常: " . $e->getMessage(), [
                'from' => $fromAddress,
                'to' => $toAddress,
                'amount' => $amountSun,
            ]);
            return null;
        }
    }

    /**
     * 签名交易（使用私钥）
     *
     * TRON使用secp256k1曲线进行ECDSA签名
     * 使用simplito/elliptic-php库实现
     *
     * @param array $transaction 未签名的交易
     * @param string $privateKey 私钥（64位hex）
     * @return array|null 签名后的交易
     */
    public function signTransaction(array $transaction, string $privateKey): ?array
    {
        // 检查GMP扩展
        if (!extension_loaded('gmp')) {
            Log::error("TRON交易签名失败：GMP扩展未安装", [
                'txID' => $transaction['txID'] ?? 'unknown',
            ]);
            return null;
        }

        // 检查elliptic-php库
        if (!class_exists('\Elliptic\EC')) {
            Log::error("TRON交易签名失败：elliptic-php库未安装", [
                'txID' => $transaction['txID'] ?? 'unknown',
                'solution' => 'composer require simplito/elliptic-php',
            ]);
            return null;
        }

        try {
            // 获取交易ID
            $txID = $transaction['txID'] ?? null;
            if (!$txID) {
                Log::error("TRON交易签名失败：交易ID不存在");
                return null;
            }

            // 验证私钥格式
            if (!preg_match('/^[0-9a-fA-F]{64}$/', $privateKey)) {
                Log::error("TRON交易签名失败：无效的私钥格式（应为64位十六进制字符串）");
                return null;
            }

            // 创建secp256k1椭圆曲线实例
            $ec = new \Elliptic\EC('secp256k1');

            // 从私钥创建密钥对
            $key = $ec->keyFromPrivate($privateKey, 'hex');

            // 对交易ID进行签名
            $signature = $key->sign($txID, ['canonical' => true]);

            // 提取签名的r和s值（各64位十六进制）
            $r = $signature->r->toString('hex', 64);
            $s = $signature->s->toString('hex', 64);

            // 获取恢复参数v（用于从签名恢复公钥）
            $v = dechex($signature->recoveryParam);

            // 组合成完整的签名（r + s + v）
            $signatureHex = $r . $s . str_pad($v, 2, '0', STR_PAD_LEFT);

            // 将签名添加到交易中
            $transaction['signature'] = [$signatureHex];

            Log::debug("TRON交易签名成功", [
                'txID' => $txID,
                'signature_length' => strlen($signatureHex),
            ]);

            return $transaction;

        } catch (\Throwable $e) {
            Log::error("TRON交易签名异常: " . $e->getMessage(), [
                'txID' => $transaction['txID'] ?? 'unknown',
                'trace' => $e->getTraceAsString(),
            ]);
            return null;
        }
    }

    /**
     * 广播已签名的交易
     * @param array $signedTransaction 已签名的交易
     * @return array ['success' => bool, 'tx_hash' => string|null, 'message' => string]
     */
    public function broadcastTransaction(array $signedTransaction): array
    {
        try {
            $headers = ['Content-Type' => 'application/json'];
            if ($this->apiKey) {
                $headers['TRON-PRO-API-KEY'] = $this->apiKey;
            }

            $client = new \GuzzleHttp\Client([
                'timeout' => 30,
                'headers' => $headers,
            ]);

            $response = $client->post($this->apiUrl . '/wallet/broadcasttransaction', [
                'json' => $signedTransaction,
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            if (isset($data['result']) && $data['result'] === true) {
                return [
                    'success' => true,
                    'tx_hash' => $signedTransaction['txID'] ?? null,
                    'message' => '交易广播成功',
                ];
            }

            $error = $data['message'] ?? $data['code'] ?? 'Unknown error';
            Log::error("广播TRON交易失败", [
                'error' => $error,
                'response' => $data,
            ]);

            return [
                'success' => false,
                'tx_hash' => null,
                'message' => "交易广播失败: {$error}",
            ];

        } catch (\Throwable $e) {
            Log::error("广播TRON交易异常: " . $e->getMessage());
            return [
                'success' => false,
                'tx_hash' => null,
                'message' => '交易广播异常: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * 完整流程：创建、签名、广播交易
     *
     * @param string $fromAddress 发送地址
     * @param string $toAddress 接收地址
     * @param int $amountSun 金额（SUN）
     * @param string $privateKey 私钥
     * @return array ['success' => bool, 'tx_hash' => string|null, 'message' => string]
     */
    public function sendTransaction(string $fromAddress, string $toAddress, int $amountSun, string $privateKey): array
    {
        // 第一步：创建交易
        $transaction = $this->createTransaction($fromAddress, $toAddress, $amountSun);
        if (!$transaction) {
            return [
                'success' => false,
                'tx_hash' => null,
                'message' => '创建交易失败',
            ];
        }

        Log::info("创建TRON交易成功", [
            'txID' => $transaction['txID'] ?? 'unknown',
            'from' => $fromAddress,
            'to' => $toAddress,
            'amount_sun' => $amountSun,
        ]);

        // 第二步：签名交易
        $signedTransaction = $this->signTransaction($transaction, $privateKey);
        if (!$signedTransaction) {
            return [
                'success' => false,
                'tx_hash' => null,
                'message' => 'TRON交易签名功能需要安装签名库（simplito/elliptic-php）',
            ];
        }

        // 第三步：广播交易
        return $this->broadcastTransaction($signedTransaction);
    }
}
