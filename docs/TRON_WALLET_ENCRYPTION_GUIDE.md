# TRON热钱包私钥加密使用指南

## 概述

为了安全地存储和使用TRON热钱包私钥，本系统实现了基于AES-256-CBC的加密机制。

## 组件说明

### 1. CryptoHelper（加密工具类）
位置：`app/lib/helper/CryptoHelper.php`

功能：
- 使用AES-256-CBC算法加密/解密数据
- 自动生成随机IV确保安全性
- 基于环境变量的密钥管理

### 2. CryptoCommand（命令行工具）
位置：`app/command/CryptoCommand.php`

功能：
- 生成加密密钥
- 加密TRON私钥
- 解密验证
- 加密功能测试

### 3. TronWebHelper::sendTransactionFromConfig()
位置：`app/lib/helper/TronWebHelper.php`

功能：
- 从群组配置自动获取并解密热钱包私钥
- 安全地发送TRX转账交易
- 自动清除内存中的私钥

## 使用步骤

### 第一步：生成加密密钥

```bash
php webman crypto --generate-key
```

输出示例：
```
===========================================
加密密钥生成成功！
===========================================

请将以下密钥添加到 .env 文件中：

APP_ENCRYPTION_KEY=a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6q7r8s9t0u1v2w3x4y5z6

⚠️ 重要提示：
1. 请妥善保管此密钥，丢失后无法解密已加密的数据
2. 切勿将密钥提交到版本控制系统
3. 生产环境应使用独立的密钥
```

### 第二步：配置加密密钥

将生成的密钥添加到 `.env` 文件：

```env
APP_ENCRYPTION_KEY=your_generated_key_here
```

### 第三步：加密TRON私钥

```bash
php webman crypto --encrypt=YOUR_64_CHAR_PRIVATE_KEY
```

输出示例：
```
===========================================
私钥加密成功！
===========================================

加密后的私钥（请存入数据库 hot_wallet_private_key 字段）：

eyJpdiI6InRlc3QxMjM0NTY3ODkwYWJjZGVmIiwiZW5jcnlwdGVkIjoi...

⚠️ 安全提示：
1. 加密后的数据存入数据库
2. 原始私钥应立即销毁
3. 切勿在日志中记录原始私钥
```

### 第四步：存入数据库

将加密后的私钥存入 `tg_game_group_config` 表的 `hot_wallet_private_key` 字段：

```sql
UPDATE tg_game_group_config
SET hot_wallet_private_key = 'eyJpdiI6InRlc3QxMjM0...',
    hot_wallet_address = 'TYourHotWalletAddressHere...'
WHERE id = 1;
```

### 第五步：使用热钱包发送交易

在代码中使用：

```php
use app\lib\helper\TronWebHelper;

// 获取群组配置
$groupConfig = $this->configRepository->findById($groupId);

// 创建TronWebHelper实例
$tronHelper = new TronWebHelper();

// 发送交易
$result = $tronHelper->sendTransactionFromConfig(
    $groupConfig,
    $toAddress,      // 接收地址
    $amountTrx       // 金额（TRX）
);

if ($result['success']) {
    echo "交易发送成功: " . $result['tx_hash'];
} else {
    echo "交易失败: " . $result['message'];
}
```

## 测试验证

### 测试加密功能

```bash
php webman crypto --test
```

### 验证解密

```bash
php webman crypto --decrypt=YOUR_ENCRYPTED_KEY
```

输出示例：
```
解密成功！私钥格式验证：
  长度: 64 字符
  格式: ✓ 有效十六进制

前8位: a1b2c3d4...
后8位: ...x5y6z7w8

⚠️ 出于安全考虑，不显示完整私钥
```

## 安全最佳实践

### 1. 密钥管理
- ✅ 使用强随机密钥
- ✅ 不同环境使用不同密钥
- ✅ 定期轮换加密密钥（需要重新加密所有私钥）
- ❌ 切勿将密钥提交到Git
- ❌ 切勿在代码中硬编码密钥

### 2. 私钥存储
- ✅ 仅存储加密后的私钥
- ✅ 限制数据库访问权限
- ✅ 启用数据库审计日志
- ❌ 切勿记录原始私钥到日志
- ❌ 切勿通过API返回私钥

### 3. 使用规范
- ✅ 使用后立即清除内存中的私钥
- ✅ 记录所有转账操作日志
- ✅ 实施金额和频率限制
- ✅ 启用多重签名验证（如适用）
- ❌ 避免在生产环境手动解密私钥

### 4. 部署建议
- 使用专用的热钱包，资金量控制在合理范围
- 大额资金存储在冷钱包
- 定期审计热钱包余额
- 配置余额告警机制

## 故障排查

### 错误：加密密钥未配置

```
错误: 加密密钥未配置
请先使用 --generate-key 生成加密密钥并配置到.env文件
```

**解决方案**：
1. 运行 `php webman crypto --generate-key`
2. 将生成的密钥添加到 `.env` 文件

### 错误：解密失败

```
解密钱包私钥失败
```

**可能原因**：
1. 加密密钥不正确
2. 数据已损坏
3. 使用了不同的加密密钥

**解决方案**：
1. 确认 `.env` 中的加密密钥正确
2. 使用正确的密钥重新加密私钥
3. 检查数据库中的数据完整性

## 待实现功能

⚠️ **注意**：要实际发送TRON交易，还需要：

1. 安装TRON API库：
```bash
composer require iexbase/tron-api
```

2. 实现 `TronWebHelper::sendTransaction()` 方法中的实际签名逻辑
   （目前该方法返回未实现错误）

3. 配置TronGrid API密钥到 `.env`：
```env
TRONGRID_API_KEY=your_trongrid_api_key
```

## 相关文件

- `app/lib/helper/CryptoHelper.php` - 加密工具类
- `app/lib/helper/TronWebHelper.php` - TRON交互助手
- `app/command/CryptoCommand.php` - CLI命令工具
- `app/model/ModelTgGameGroupConfig.php` - 群组配置模型

## 联系支持

如有问题，请查看日志文件或联系技术团队。
