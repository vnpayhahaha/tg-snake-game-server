# TRON交易签名配置指南

## 当前状态

✅ 已完成：
- 加密工具类（CryptoHelper）- AES-256加密私钥
- 交易助手类（TronTransactionHelper）- 创建、签名和广播交易
- 热钱包配置（从数据库获取并解密私钥）
- CLI工具（私钥加密管理）
- TRON交易签名功能（secp256k1 ECDSA签名）
- GMP扩展（已安装）
- simplito/elliptic-php库（已安装）

⚠️ 需要配置：
- 加密密钥（APP_ENCRYPTION_KEY）
- 热钱包私钥（加密后存入数据库）
- TRONGRID_API_KEY（强烈推荐）
- 建议在测试网进行完整测试

## 问题说明

TRON区块链使用 **secp256k1** 椭圆曲线进行ECDSA签名（与比特币、以太坊相同）。
PHP 8.3 的 OpenSSL 扩展不直接支持 secp256k1 曲线，需要使用以下方案之一。

## 解决方案

### 方案A：安装 GMP + simplito/elliptic-php（推荐）

#### 步骤1：安装GMP扩展

**Docker环境**：
```dockerfile
# 在Dockerfile中添加
RUN apk add --no-cache gmp-dev \
    && docker-php-ext-install gmp

# 或使用apt（Debian/Ubuntu）
RUN apt-get update && apt-get install -y libgmp-dev \
    && docker-php-ext-install gmp
```

**非Docker环境**：
```bash
# CentOS/RHEL
sudo yum install php-gmp
sudo systemctl restart php-fpm

# Ubuntu/Debian
sudo apt-get install php-gmp
sudo systemctl restart php-fpm

# macOS (Homebrew)
brew install gmp
pecl install gmp
```

#### 步骤2：安装签名库

```bash
docker exec hyper bash -c "cd /data/project/tg-snake/tg-snake-game && composer require simplito/elliptic-php"
```

#### 步骤3：实现签名方法

在 `TronTransactionHelper.php` 的 `signTransaction()` 方法中添加：

```php
public function signTransaction(array $transaction, string $privateKey): ?array
{
    if (!extension_loaded('gmp')) {
        Log::error("GMP扩展未安装，无法进行ECDSA签名");
        return null;
    }

    if (!class_exists('\Elliptic\EC')) {
        Log::error("elliptic-php库未安装");
        return null;
    }

    try {
        $ec = new \Elliptic\EC('secp256k1');
        $key = $ec->keyFromPrivate($privateKey, 'hex');

        $txID = $transaction['txID'] ?? null;
        if (!$txID) {
            throw new \Exception('交易ID不存在');
        }

        // 签名
        $signature = $key->sign($txID, ['canonical' => true]);
        $r = $signature->r->toString('hex', 64);
        $s = $signature->s->toString('hex', 64);
        $v = dechex($signature->recoveryParam);

        $signatureHex = $r . $s . str_pad($v, 2, '0', STR_PAD_LEFT);

        $transaction['signature'] = [$signatureHex];

        return $transaction;

    } catch (\Throwable $e) {
        Log::error("TRON交易签名失败: " . $e->getMessage());
        return null;
    }
}
```

### 方案B：使用外部签名服务

适用于高安全性要求的生产环境。

#### 架构设计
```
Web应用 → 签名服务API → 硬件安全模块(HSM)/密钥管理服务
```

#### 实现示例

```php
public function signTransaction(array $transaction, string $privateKey): ?array
{
    // 调用外部签名服务
    $signingServiceUrl = getenv('TRON_SIGNING_SERVICE_URL');

    $client = new \GuzzleHttp\Client();
    $response = $client->post($signingServiceUrl . '/sign', [
        'json' => [
            'txID' => $transaction['txID'],
            'keyId' => 'tron-hot-wallet-key', // 密钥标识符，不传递实际私钥
        ],
        'headers' => [
            'Authorization' => 'Bearer ' . getenv('SIGNING_SERVICE_TOKEN'),
        ],
    ]);

    $result = json_decode($response->getBody()->getContents(), true);

    if (isset($result['signature'])) {
        $transaction['signature'] = [$result['signature']];
        return $transaction;
    }

    return null;
}
```

### 方案C：使用TronLink或硬件钱包

适用于手动操作或需要用户确认的场景。

1. 应用创建未签名交易
2. 导出交易数据给TronLink或硬件钱包
3. 用户在设备上确认并签名
4. 应用广播已签名交易

## 完整使用流程

### 1. 配置环境变量

```env
# .env文件
APP_ENCRYPTION_KEY=your_generated_encryption_key
TRONGRID_API_KEY=your_trongrid_api_key
TRON_API_URL=https://api.trongrid.io
```

### 2. 加密并存储私钥

```bash
# 生成加密密钥
php webman crypto --generate-key

# 加密私钥
php webman crypto --encrypt=YOUR_PRIVATE_KEY

# 将加密后的私钥存入数据库
UPDATE tg_game_group_config
SET hot_wallet_private_key = 'ENCRYPTED_KEY',
    hot_wallet_address = 'YOUR_HOT_WALLET_ADDRESS'
WHERE id = 1;
```

### 3. 在代码中使用

```php
use app\lib\helper\TronWebHelper;

$tronHelper = new TronWebHelper();

// 方式1：直接从群组配置发送
$result = $tronHelper->sendTransactionFromConfig(
    $groupConfig,
    $recipientAddress,
    $amountTrx
);

// 方式2：手动指定参数
$result = $tronHelper->sendTransaction(
    $fromAddress,
    $toAddress,
    TronWebHelper::trxToSun($amountTrx),
    $privateKey
);

if ($result['success']) {
    echo "交易成功: {$result['tx_hash']}";
    // 记录交易
    $this->logOutgoingTransaction($groupId, [
        'tx_hash' => $result['tx_hash'],
        'from_address' => $fromAddress,
        'to_address' => $toAddress,
        'amount' => $amountSun,
    ]);
} else {
    echo "交易失败: {$result['message']}";
}
```

## 测试

### 测试网配置

```env
TRON_API_URL=https://api.shasta.trongrid.io  # Shasta测试网
TRONGRID_API_KEY=your_test_api_key
```

### 获取测试币

1. 访问 https://www.trongrid.io/shasta
2. 输入测试钱包地址
3. 领取测试TRX

### 测试脚本

```php
// test_tron_transaction.php
require __DIR__ . '/vendor/autoload.php';

use app\lib\helper\TronWebHelper;
use app\lib\helper\CryptoHelper;

// 测试加密
$testKey = '0123456789abcdef0123456789abcdef0123456789abcdef0123456789abcdef';
$encrypted = CryptoHelper::encrypt($testKey);
$decrypted = CryptoHelper::decrypt($encrypted);

assert($testKey === $decrypted, '加密解密测试失败');
echo "✓ 加密功能测试通过\n";

// 测试交易创建（不广播）
$tronHelper = new TronWebHelper('https://api.shasta.trongrid.io');
$transaction = $tronHelper->createTransaction(
    'YOUR_TEST_FROM_ADDRESS',
    'YOUR_TEST_TO_ADDRESS',
    1000000  // 1 TRX
);

if ($transaction) {
    echo "✓ 交易创建测试通过\n";
    echo "  txID: {$transaction['txID']}\n";
} else {
    echo "✗ 交易创建失败\n";
}
```

## 安全检查清单

- [x] GMP扩展已安装
- [x] simplito/elliptic-php库已安装
- [ ] APP_ENCRYPTION_KEY已配置且足够强
- [ ] TRONGRID_API_KEY已配置
- [ ] 私钥已加密存储到数据库
- [ ] 原始私钥已从所有日志和临时文件中清除
- [ ] 数据库访问权限已限制
- [ ] API密钥权限设置为最小必需
- [ ] 热钱包余额控制在合理范围
- [ ] 大额资金已转移到冷钱包
- [ ] 交易日志记录已启用
- [ ] 告警机制已配置

## 生产环境建议

1. **分离热冷钱包**
   - 热钱包：日常小额转账（<10,000 TRX）
   - 冷钱包：大额资金存储

2. **多重签名**
   - 重要操作需要多人确认
   - 使用TRON多签账户

3. **监控告警**
   - 余额变动告警
   - 异常交易告警
   - 高频交易告警

4. **审计日志**
   - 记录所有转账操作
   - 记录私钥访问
   - 定期审计

5. **灾难恢复**
   - 备份加密密钥（离线存储）
   - 备份热钱包私钥（加密后离线存储）
   - 制定私钥轮换计划

## 故障排查

### 错误：GMP扩展未安装
```
解决：参考"方案A步骤1"安装GMP扩展
```

### 错误：签名失败
```
检查：
1. GMP扩展是否正常加载
2. simplito/elliptic-php是否安装
3. 私钥格式是否正确（64位hex）
```

### 错误：交易广播失败
```
可能原因：
1. 签名不正确
2. 账户余额不足
3. 网络问题
4. API密钥权限不足
```

## 相关链接

- TronGrid文档: https://developers.tron.network/docs
- TRON测试网: https://www.trongrid.io/shasta
- simplito/elliptic-php: https://github.com/simplito/elliptic-php
- PHP GMP文档: https://www.php.net/manual/en/book.gmp.php

## 技术支持

如遇到问题，请提供以下信息：
1. PHP版本和扩展列表 (`php -m`)
2. Composer依赖列表 (`composer show`)
3. 错误日志
4. 测试网交易哈希（如有）
