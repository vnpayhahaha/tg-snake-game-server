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
     * @return array|null ['success' => bool, 'tx_hash' => string, 'message' => string]
     */
    public function sendTransaction(string $fromAddress, string $toAddress, int $amount, string $privateKey): ?array
    {
        /*
         * TRON交易签名和广播实现指南
         *
         * 方案一：使用iexbase/tron-api库（推荐）
         * ==========================================
         * 1. 安装依赖：
         *    composer require iexbase/tron-api
         *
         * 2. 实现代码：
         *    use IEXBase\TronAPI\Tron;
         *
         *    $tron = new Tron();
         *    $tron->setAddress($fromAddress);
         *    $tron->setPrivateKey($privateKey);
         *
         *    // 创建转账交易
         *    $transaction = $tron->sendTransaction($toAddress, $amount);
         *
         *    if ($transaction && isset($transaction['txid'])) {
         *        return [
         *            'success' => true,
         *            'tx_hash' => $transaction['txid'],
         *            'message' => '交易发送成功'
         *        ];
         *    }
         *
         *    return [
         *        'success' => false,
         *        'message' => '交易发送失败'
         *    ];
         *
         *
         * 方案二：使用TronGrid API + 手动签名
         * ==========================================
         * 1. 创建交易：
         *    POST https://api.trongrid.io/wallet/createtransaction
         *    Body: {
         *        "to_address": "Base58编码的接收地址",
         *        "owner_address": "Base58编码的发送地址",
         *        "amount": SUN数量（整数）
         *    }
         *
         * 2. 签名交易（需要私钥签名库）：
         *    - 使用kornrunner/keccak进行Keccak256哈希
         *    - 使用Elliptic-PHP进行ECDSA签名
         *
         *    composer require kornrunner/keccak
         *    composer require simplito/elliptic-php
         *
         *    use kornrunner\Keccak;
         *    use Elliptic\EC;
         *
         *    $txID = $transaction['txID'];
         *    $ec = new EC('secp256k1');
         *    $key = $ec->keyFromPrivate($privateKey);
         *    $signature = $key->sign($txID, 'hex', ['canonical' => true]);
         *
         * 3. 广播交易：
         *    POST https://api.trongrid.io/wallet/broadcasttransaction
         *    Body: {
         *        "visible": false,
         *        "txID": "交易ID",
         *        "raw_data": {...},  // 原始交易数据
         *        "signature": ["签名后的字符串"]
         *    }
         *
         *
         * 方案三：使用TronWeb JavaScript库（通过PHP执行）
         * ==========================================
         * 适用于已有Node.js环境的场景
         *
         *
         * 安全注意事项：
         * ==========================================
         * 1. 私钥必须加密存储，不得明文保存在数据库或日志中
         * 2. 交易签名应在安全环境中进行
         * 3. 建议使用热钱包与冷钱包分离的架构
         * 4. 生产环境必须配置TronGrid API Key
         * 5. 实现交易重放攻击防护
         * 6. 添加交易金额和频率限制
         */

        // 验证地址格式
        if (!self::isValidAddress($fromAddress) || !self::isValidAddress($toAddress)) {
            return [
                'success' => false,
                'message' => '无效的TRON地址格式'
            ];
        }

        // 验证金额
        if ($amount <= 0) {
            return [
                'success' => false,
                'message' => '转账金额必须大于0'
            ];
        }

        // 验证私钥（基本格式检查）
        if (strlen($privateKey) != 64) {
            return [
                'success' => false,
                'message' => '无效的私钥格式（应为64位十六进制字符串）'
            ];
        }

        /*
         * ⚠️ 重要：此功能涉及私钥管理和资金安全
         *
         * 使用TronTransactionHelper进行交易签名和广播
         *
         * 前置要求：
         * 1. 安装GMP扩展：docker-php-ext-install gmp
         * 2. 安装签名库：composer require simplito/elliptic-php
         * 3. 配置TRONGRID_API_KEY
         *
         * 详细配置说明：docs/TRON_TRANSACTION_SIGNING_SETUP.md
         */

        try {
            $transactionHelper = new TronTransactionHelper($this->apiUrl, $this->apiKey);

            Log::info("开始发送TRON交易", [
                'from' => $fromAddress,
                'to' => $toAddress,
                'amount_sun' => $amount,
                'amount_trx' => self::sunToTrx($amount),
            ]);

            $result = $transactionHelper->sendTransaction(
                $fromAddress,
                $toAddress,
                $amount,
                $privateKey
            );

            // 清除私钥变量
            $privateKey = null;
            unset($privateKey);

            if ($result['success']) {
                Log::info("TRON交易发送成功", [
                    'tx_hash' => $result['tx_hash'],
                    'from' => $fromAddress,
                    'to' => $toAddress,
                ]);
            } else {
                Log::warning("TRON交易发送失败", [
                    'message' => $result['message'],
                    'from' => $fromAddress,
                    'to' => $toAddress,
                ]);
            }

            return $result;

        } catch (\Throwable $e) {
            // 清除私钥变量
            $privateKey = null;
            unset($privateKey);

            Log::error("TRON交易发送异常: " . $e->getMessage(), [
                'from' => $fromAddress,
                'to' => $toAddress,
                'amount' => $amount,
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => '交易发送异常: ' . $e->getMessage()
            ];
        }
    }

    /**
     * 从群组配置发送TRX转账
     * @param object $groupConfig 群组配置对象（ModelTgGameGroupConfig）
     * @param string $toAddress 接收地址
     * @param float $amountTrx 金额（TRX）
     * @return array ['success' => bool, 'tx_hash' => string|null, 'message' => string]
     */
    public function sendTransactionFromConfig($groupConfig, string $toAddress, float $amountTrx): array
    {
        try {
            // 验证配置对象
            if (!isset($groupConfig->hot_wallet_address) || !isset($groupConfig->hot_wallet_private_key)) {
                return [
                    'success' => false,
                    'message' => '群组配置缺少热钱包地址或私钥'
                ];
            }

            // 检查热钱包配置
            if (empty($groupConfig->hot_wallet_address)) {
                return [
                    'success' => false,
                    'message' => '热钱包地址未配置'
                ];
            }

            if (empty($groupConfig->hot_wallet_private_key)) {
                return [
                    'success' => false,
                    'message' => '热钱包私钥未配置'
                ];
            }

            // 验证加密密钥是否配置
            if (!CryptoHelper::isKeyConfigured()) {
                Log::error("加密密钥未配置，无法解密热钱包私钥");
                return [
                    'success' => false,
                    'message' => '系统加密配置错误，请联系管理员'
                ];
            }

            // 解密私钥
            try {
                $privateKey = CryptoHelper::decrypt($groupConfig->hot_wallet_private_key);
            } catch (\Throwable $e) {
                Log::error("解密热钱包私钥失败: " . $e->getMessage(), [
                    'group_id' => $groupConfig->id ?? 'unknown'
                ]);
                return [
                    'success' => false,
                    'message' => '解密钱包私钥失败'
                ];
            }

            // 转换金额为SUN
            $amountSun = self::trxToSun($amountTrx);

            Log::info("准备从热钱包发送交易", [
                'group_id' => $groupConfig->id ?? 'unknown',
                'from' => $groupConfig->hot_wallet_address,
                'to' => $toAddress,
                'amount_trx' => $amountTrx,
                'amount_sun' => $amountSun,
            ]);

            // 调用发送交易方法
            $result = $this->sendTransaction(
                $groupConfig->hot_wallet_address,
                $toAddress,
                $amountSun,
                $privateKey
            );

            // 清除私钥变量（安全措施）
            $privateKey = null;
            unset($privateKey);

            return $result;

        } catch (\Throwable $e) {
            Log::error("从群组配置发送交易失败: " . $e->getMessage(), [
                'group_id' => $groupConfig->id ?? 'unknown',
                'to_address' => $toAddress,
                'amount_trx' => $amountTrx,
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => '发送交易失败: ' . $e->getMessage()
            ];
        }
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

    /**
     * 获取钱包地址的交易历史
     * @param string $address 钱包地址
     * @param int $minBlockHeight 最小区块高度（从此高度开始查询）
     * @param int $limit 返回记录数限制
     * @return array 交易列表
     */
    public function getTransactionHistory(string $address, int $minBlockHeight = 0, int $limit = 200): array
    {
        // 验证地址格式
        if (!self::isValidAddress($address)) {
            Log::error("TronWebHelper::getTransactionHistory - 无效的地址格式", [
                'address' => $address
            ]);
            return [];
        }

        try {
            $params = [
                'only_confirmed' => 'true',
                'only_to' => 'true',    // 只获取转入交易
                'limit' => min($limit, 200), // TronGrid API最大支持200
            ];

            $queryString = http_build_query($params);
            $url = $this->apiUrl . "/v1/accounts/{$address}/transactions?{$queryString}";

            $response = $this->httpClient->get($url);
            $data = json_decode($response->getBody()->getContents(), true);

            if (!isset($data['data']) || !is_array($data['data'])) {
                Log::warning("TronGrid API返回数据格式异常", [
                    'address' => $address,
                    'response' => $data
                ]);
                return [];
            }

            // 解析并过滤交易
            $transactions = $this->parseTransactions($data['data'], $minBlockHeight);

            Log::debug("TronWebHelper::getTransactionHistory - 成功获取交易", [
                'address' => $address,
                'min_block_height' => $minBlockHeight,
                'total_fetched' => count($data['data']),
                'filtered_count' => count($transactions),
            ]);

            return $transactions;

        } catch (\Throwable $e) {
            Log::error("获取TRON交易历史失败: " . $e->getMessage(), [
                'address' => $address,
                'min_block_height' => $minBlockHeight,
                'trace' => $e->getTraceAsString()
            ]);
            return [];
        }
    }

    /**
     * 解析TRON交易数据（辅助方法）
     * @param array $transactions TronGrid API返回的原始交易数据
     * @param int $minBlockHeight 最小区块高度
     * @return array 标准化的交易数组
     */
    protected function parseTransactions(array $transactions, int $minBlockHeight = 0): array
    {
        $result = [];

        foreach ($transactions as $tx) {
            $blockNumber = $tx['blockNumber'] ?? 0;

            // 过滤区块高度
            if ($blockNumber <= $minBlockHeight) {
                continue;
            }

            // 检查交易状态
            $status = ($tx['ret'][0]['contractRet'] ?? '') === 'SUCCESS' ? 'SUCCESS' : 'FAILED';

            // 获取合约参数
            $contract = $tx['raw_data']['contract'][0] ?? [];
            $parameter = $contract['parameter']['value'] ?? [];

            // 转换地址格式（HEX to Base58）
            $fromAddress = $this->hexToBase58($parameter['owner_address'] ?? '');
            $toAddress = $this->hexToBase58($parameter['to_address'] ?? '');

            $result[] = [
                'tx_hash' => $tx['txID'] ?? '',
                'from_address' => $fromAddress,
                'to_address' => $toAddress,
                'amount' => (int)($parameter['amount'] ?? 0),
                'block_height' => $blockNumber,
                'block_timestamp' => (int)(($tx['block_timestamp'] ?? 0) / 1000), // 毫秒转秒
                'status' => $status,
            ];
        }

        return $result;
    }

    /**
     * 将HEX地址转换为Base58地址
     * @param string $hexAddress HEX格式地址
     * @return string Base58格式地址
     */
    protected function hexToBase58(string $hexAddress): string
    {
        if (empty($hexAddress)) {
            return '';
        }

        // 如果已经是Base58格式（以T开头），直接返回
        if (self::isValidAddress($hexAddress)) {
            return $hexAddress;
        }

        try {
            // 使用TronGrid API进行地址转换
            $response = $this->httpClient->post($this->apiUrl . '/wallet/hextoaddress', [
                'json' => [
                    'value' => $hexAddress,
                    'visible' => true,
                ],
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            if (isset($data['base58checkAddress'])) {
                return $data['base58checkAddress'];
            }

            // 如果API返回了visible格式的地址
            if (isset($data['address'])) {
                return $data['address'];
            }

            Log::warning("HEX地址转换失败: API返回格式异常", [
                'hex_address' => $hexAddress,
                'response' => $data
            ]);

            return $hexAddress;

        } catch (\Throwable $e) {
            Log::error("HEX地址转换异常: " . $e->getMessage(), [
                'hex_address' => $hexAddress,
            ]);
            // 转换失败时返回原始值
            return $hexAddress;
        }
    }

    /**
     * 获取当前区块高度
     * @return int|null 当前区块高度
     */
    public function getCurrentBlockHeight(): ?int
    {
        /*
         * 获取当前区块高度
         *
         * TronGrid API:
         * POST https://api.trongrid.io/wallet/getnowblock
         *
         * 响应：
         * {
         *   "block_header": {
         *     "raw_data": {
         *       "number": 12345678
         *     }
         *   }
         * }
         */

        try {
            $response = $this->httpClient->post('https://api.trongrid.io/wallet/getnowblock');
            $data = json_decode($response->getBody()->getContents(), true);
            return $data['block_header']['raw_data']['number'] ?? null;
        } catch (\Throwable $e) {
            Log::error("获取TRON区块高度失败: " . $e->getMessage());
            return null;
        }
    }
}
