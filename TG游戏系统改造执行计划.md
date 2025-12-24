# Telegram贪吃蛇游戏系统改造执行报告

> 生成时间：2024-12-24
> 项目：Telegram Snake Chain Game（基于现有支付SAAS系统改造）

---

## 一、项目现状分析

### 1.1 已完成工作
✅ **数据库模型层（10个TG前缀模型）：**
- `ModelTgGameGroup` - 游戏群组
- `ModelTgGameGroupConfig` - 群组配置
- `ModelTgGameGroupConfigLog` - 配置变更日志
- `ModelTgSnakeNode` - 蛇身节点
- `ModelTgPrizeRecord` - 中奖记录
- `ModelTgPrizeTransfer` - 奖金转账
- `ModelTgPrizeDispatchQueue` - 奖金分配队列
- `ModelTgPlayerWalletBinding` - 玩家钱包绑定
- `ModelTgPlayerWalletBindingLog` - 绑定日志
- `ModelTgTronTransactionLog` - TRON交易日志

### 1.2 现有架构优势
✅ **清晰的分层架构：** Controller → Service → Repository → Model
✅ **成熟的基础设施：** 注解路由、JWT认证、事件驱动、Redis队列
✅ **完善的权限系统：** 基于角色的权限控制、操作日志
✅ **多租户架构：** 可直接复用租户管理体系

---

## 二、改造策略

### 2.1 核心原则
1. **保留原有架构**：沿用 Controller → Service → Repository → Model 的分层
2. **复用基础设施**：复用租户系统、权限系统、事件系统
3. **保持代码风格**：遵循现有的注解路由、依赖注入、命名规范
4. **独立模块设计**：TG游戏功能作为独立模块，不影响现有支付业务

### 2.2 架构设计

```
┌─────────────────────────────────────────────────────────────┐
│                     Telegram Bot API                        │
│                  (命令处理 + 消息推送)                       │
└─────────────────────┬───────────────────────────────────────┘
                      │
┌─────────────────────▼───────────────────────────────────────┐
│              http/backend/controller/                       │
│  - TgGameGroupController (群组管理)                         │
│  - TgSnakeGameController (游戏监控)                         │
│  - TgPrizeRecordController (中奖记录)                       │
│  - TgStatisticsController (数据统计)                        │
└─────────────────────┬───────────────────────────────────────┘
                      │
┌─────────────────────▼───────────────────────────────────────┐
│                app/service/ (业务逻辑层)                    │
│  - TgGameGroupService (群组配置)                            │
│  - TgSnakeGameService (游戏核心逻辑)                        │
│  - TgPrizeService (中奖处理)                                │
│  - TgTronMonitorService (区块链监听)                        │
│  - TgTransferService (自动转账)                             │
│  - TgTelegramBotService (机器人服务)                        │
│  - TgPlayerWalletService (钱包绑定)                         │
│  - TgWalletChangeService (钱包变更)                         │
└─────────────────────┬───────────────────────────────────────┘
                      │
┌─────────────────────▼───────────────────────────────────────┐
│              app/repository/ (数据访问层)                   │
│  - TgGameGroupRepository                                    │
│  - TgGameGroupConfigRepository                              │
│  - TgSnakeNodeRepository                                    │
│  - TgPrizeRecordRepository                                  │
│  - TgPrizeTransferRepository                                │
│  - TgPlayerWalletBindingRepository                          │
│  - TgTronTransactionLogRepository                           │
└─────────────────────┬───────────────────────────────────────┘
                      │
┌─────────────────────▼───────────────────────────────────────┐
│                 app/model/ (已完成)                         │
│              10个TG前缀模型（已创建）                        │
└─────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────┐
│                   后台进程/队列任务                          │
│  - TronMonitorCrontab (TRON监听定时任务)                    │
│  - WalletChangeNotifierCrontab (钱包变更通知)               │
│  - PrizeTransferQueueConsumer (奖金转账队列)                │
│  - TronTxProcessQueueConsumer (交易处理队列)                │
└─────────────────────────────────────────────────────────────┘
```

---

## 三、详细实施计划

### 阶段一：Repository层（数据访问层）

**优先级：⭐⭐⭐⭐⭐**
**工作量：7个Repository类**

#### 1. TgGameGroupRepository
```php
位置：app/repository/TgGameGroupRepository.php
继承：IRepository
注入：ModelTgGameGroup
方法：
  - handleSearch() - 实现群组搜索（按tenant_id、tg_chat_id、status）
  - getByTgChatId() - 根据Telegram群组ID查询
  - updatePrizePool() - 更新奖池金额
  - updateSnakeNodes() - 更新蛇身节点
```

#### 2. TgGameGroupConfigRepository
```php
位置：app/repository/TgGameGroupConfigRepository.php
继承：IRepository
注入：ModelTgGameGroupConfig
方法：
  - handleSearch() - 实现配置搜索
  - getActiveConfig() - 获取生效中的配置
  - getByTenantId() - 按租户查询配置
  - checkWalletChangeStatus() - 检查钱包变更状态
  - getByTgChatId() - 根据Telegram群组ID查询配置
```

#### 3. TgSnakeNodeRepository
```php
位置：app/repository/TgSnakeNodeRepository.php
继承：IRepository
注入：ModelTgSnakeNode
方法：
  - handleSearch() - 实现节点搜索
  - getActiveNodes() - 获取活跃节点（按group_id和创建时间排序）
  - getNodesByWalletCycle() - 按钱包周期查询
  - archiveNodes() - 归档节点（钱包变更时）
  - countDailyNodes() - 统计当日节点数
  - getNodesBetween() - 获取区间内的节点
  - findByTxHash() - 根据交易哈希查询（防重复）
```

#### 4. TgPrizeRecordRepository
```php
位置：app/repository/TgPrizeRecordRepository.php
继承：IRepository
注入：ModelTgPrizeRecord
方法：
  - handleSearch() - 实现中奖记录搜索
  - getByWalletCycle() - 按钱包周期查询
  - getPendingTransfers() - 获取待转账记录
  - getStatistics() - 统计中奖数据（总金额、总次数等）
  - getByGroupId() - 按群组查询中奖记录
```

#### 5. TgPrizeTransferRepository
```php
位置：app/repository/TgPrizeTransferRepository.php
继承：IRepository
注入：ModelTgPrizeTransfer
方法：
  - handleSearch() - 实现转账记录搜索
  - getPendingTransfers() - 获取待处理转账
  - updateTransferStatus() - 更新转账状态
  - getFailedTransfers() - 获取失败转账
  - getByPrizeRecordId() - 根据中奖记录ID查询所有转账
```

#### 6. TgPlayerWalletBindingRepository
```php
位置：app/repository/TgPlayerWalletBindingRepository.php
继承：IRepository
注入：ModelTgPlayerWalletBinding
方法：
  - handleSearch() - 实现绑定记录搜索
  - getByTgUserId() - 根据TG用户ID查询（指定群组）
  - getByWalletAddress() - 根据钱包地址查询
  - unbindWallet() - 解绑钱包
  - getActiveBinding() - 获取有效绑定
```

#### 7. TgTronTransactionLogRepository
```php
位置：app/repository/TgTronTransactionLogRepository.php
继承：IRepository
注入：ModelTgTronTransactionLog
方法：
  - handleSearch() - 实现交易日志搜索
  - getUnprocessedTx() - 获取未处理交易
  - markAsProcessed() - 标记已处理
  - existsByTxHash() - 检查交易是否已存在
  - getLastProcessedBlock() - 获取最后处理的区块高度
```

---

### 阶段二：Service层（业务逻辑层）

**优先级：⭐⭐⭐⭐⭐**
**工作量：8个Service类**

#### 1. TgGameGroupService
```php
位置：app/service/TgGameGroupService.php
继承：IService
注入：TgGameGroupRepository
功能：
  - 群组配置管理（创建、更新、查询）
  - 群组状态管理（启用、停用）
  - 游戏数据统计
  - 继承IService的标准CRUD方法
```

#### 2. TgSnakeGameService（核心游戏逻辑）⭐⭐⭐⭐⭐
```php
位置：app/service/TgSnakeGameService.php
继承：BaseService
注入：TgSnakeNodeRepository, TgGameGroupRepository
功能：
  ✅ 购彩凭证生成
    - extractTicketNumber(string $txHash): string
      从交易哈希提取两位数字（从右向左）
    - generateTicketSerialNo(int $groupId): string
      生成流水号：YYYYMMDD-序号

  ✅ 蛇身拼接
    - appendSnakeNode(array $nodeData): SnakeNode
      创建新节点并追加到蛇尾
    - getCurrentSnakeChain(int $groupId): array
      获取当前活跃的蛇身链

  ✅ 中奖匹配算法（核心）
    - checkMatch(int $groupId, SnakeNode $newNode): ?array
      检查新节点是否触发中奖
    - 规则1：连号清空奖池（与上一个节点相同）
      返回：['type' => 'jackpot', 'nodes' => [...]]
    - 规则2：区间匹配（与历史节点匹配）
      返回：['type' => 'range', 'start' => $node1, 'end' => $node2, 'nodes' => [...]]

  ✅ 蛇身管理
    - removeMatchedNodes(array $nodeIds): void
      标记节点为已中奖状态
    - getNodesBetween(int $startNodeId, int $endNodeId): array
      获取区间内所有节点
```

#### 3. TgPrizeService（中奖处理）⭐⭐⭐⭐⭐
```php
位置：app/service/TgPrizeService.php
继承：IService
注入：TgPrizeRecordRepository, TgPrizeTransferRepository
功能：
  ✅ 中奖记录创建
    - createPrizeRecord(array $matchData): PrizeRecord
      创建中奖记录并生成开奖流水号

  ✅ 奖金计算
    - calculatePrize(array $nodes, float $feeRate): array
      计算：total_amount, platform_fee, prize_pool, prize_per_winner
      公式：奖池 = 总金额 * (1 - 手续费比例)

  ✅ 中奖通知
    - sendPrizeNotification(PrizeRecord $prize): void
      通过Telegram Bot推送中奖消息

  ✅ 转账管理
    - createTransferRecords(PrizeRecord $prize): array
      为每个中奖玩家创建转账记录
    - triggerTransfer(PrizeRecord $prize): void
      推入转账队列处理
```

#### 4. TgTronMonitorService（区块链监听）⭐⭐⭐⭐⭐
```php
位置：app/service/TgTronMonitorService.php
继承：BaseService
注入：TgTronTransactionLogRepository, TgGameGroupConfigRepository
功能：
  ✅ 交易监听
    - fetchNewTransactions(string $walletAddress, int $lastBlock): array
      调用TronGrid API查询新交易
      轮询策略：每3秒执行一次

  ✅ 交易验证
    - validateTransaction(array $tx, GameGroupConfig $config): bool
      检查项：
      1. 金额是否等于配置的投注金额
      2. 接收地址是否匹配
      3. 交易状态是否为SUCCESS
      4. 交易哈希是否重复

  ✅ 交易处理
    - processTransaction(array $tx, int $groupId): void
      1. 记录交易日志
      2. 验证交易有效性
      3. 推入处理队列（延迟处理等待确认）

  ✅ 区块确认
    - waitForConfirmation(string $txHash, int $requiredBlocks = 19): bool
      等待区块确认（19个区块约57秒）
```

#### 5. TgTransferService（自动转账）⭐⭐⭐⭐
```php
位置：app/service/TgTransferService.php
继承：BaseService
注入：TgPrizeTransferRepository
功能：
  ✅ 转账前检查
    - checkHotWalletBalance(string $hotWalletAddress): float
      查询热钱包余额
    - validateTransferAmount(float $amount, float $balance): bool
      验证余额是否充足

  ✅ 执行转账
    - transfer(string $from, string $to, float $amount, string $privateKey): string
      调用TronWeb API执行转账
      返回：交易哈希

  ✅ 转账确认
    - confirmTransfer(string $txHash): bool
      等待转账确认（19个区块）
      更新转账状态为成功

  ✅ 异常处理
    - handleTransferFailure(PrizeTransfer $transfer, Exception $e): void
      记录错误信息
      标记转账失败
      重试次数 < 3 则重新推入队列

  ✅ 转账通知
    - sendTransferNotification(PrizeTransfer $transfer): void
      转账成功后通知玩家
```

#### 6. TgTelegramBotService（机器人服务）⭐⭐⭐⭐
```php
位置：app/service/TgTelegramBotService.php
继承：BaseService
功能：
  ✅ 命令处理
    - handleCommand(array $update): void
      解析并处理Telegram命令
      支持命令：
      /start, /help, /bind, /wallet, /cancelwallet
      /setbet, /setfee, /info, /address
      /bindwallet, /unbindwallet, /mywallet
      /snake, /mytickets, /ticket, /stats, /history, /rules

  ✅ 消息推送
    - sendMessage(int $chatId, string $text): bool
      发送群组消息
    - sendPrivateMessage(int $userId, string $text): bool
      发送私聊消息

  ✅ 通知模板
    - formatPurchaseNotification(SnakeNode $node): string
      购彩成功通知
    - formatPrizeNotification(PrizeRecord $prize): string
      中奖通知
    - formatTransferNotification(PrizeTransfer $transfer): string
      转账完成通知
    - formatWalletChangeNotification(array $data): string
      钱包变更通知

  ✅ Webhook处理
    - handleWebhook(array $update): void
      处理Telegram Bot Webhook回调
```

#### 7. TgPlayerWalletService（钱包绑定）⭐⭐⭐
```php
位置：app/service/TgPlayerWalletService.php
继承：IService
注入：TgPlayerWalletBindingRepository
功能：
  ✅ 钱包绑定
    - bindWallet(int $groupId, int $tgUserId, string $username, string $address): bool
      验证地址格式
      创建或更新绑定记录
      记录绑定日志

  ✅ 钱包解绑
    - unbindWallet(int $groupId, int $tgUserId): bool
      标记绑定为无效
      记录解绑时间和日志

  ✅ 查询绑定
    - getBindingInfo(int $groupId, int $tgUserId): ?Binding
      查询用户的钱包绑定信息
    - getBindingByAddress(int $groupId, string $address): ?Binding
      根据钱包地址反查绑定信息

  ✅ 地址验证
    - isValidTronAddress(string $address): bool
      验证TRON地址格式（T开头，34位字符）
```

#### 8. TgWalletChangeService（钱包变更）⭐⭐⭐⭐
```php
位置：app/service/TgWalletChangeService.php
继承：BaseService
注入：TgGameGroupConfigRepository, TgSnakeNodeRepository
功能：
  ✅ 开始变更
    - startWalletChange(int $groupId, string $newAddress, string $adminUsername): void
      1. 验证新地址格式
      2. 检查当前状态（不能在变更中）
      3. 更新配置：pending_wallet_address, wallet_change_status=2
      4. 设置开始时间和结束时间（10分钟后）
      5. 发送开始变更通知

  ✅ 取消变更
    - cancelWalletChange(int $groupId, string $adminUsername): void
      1. 验证是否在变更中
      2. 清除变更状态
      3. 发送取消通知

  ✅ 完成变更
    - completeWalletChange(int $groupId): void
      开启数据库事务：
      1. 归档所有活跃节点（status=1 → status=4）
      2. 增加钱包变更次数（wallet_change_count + 1）
      3. 更新配置：wallet_address = pending_wallet_address
      4. 清除变更状态
      5. 记录变更日志
      6. 提交事务
      7. 发送完成通知（显示归档节点数、清空奖池金额）

  ✅ 倒计时通知
    - sendCountdownNotification(int $groupId, int $remainingSeconds): void
      发送剩余时间提醒（每10秒）
```

---

### 阶段三：Controller层（HTTP接口）

**优先级：⭐⭐⭐⭐**
**工作量：4个Controller类**

#### 1. TgGameGroupController（后台管理）
```php
位置：http/backend/controller/TgGameGroupController.php
继承：BasicController
注解：#[RestController("/admin/tg-game-group")]
注入：TgGameGroupService, TgGameGroupConfigService

接口列表：
  GET    /list              - 群组列表（分页）
    #[GetMapping('/list')]
    #[Permission(code: 'tg:group:list')]
    #[OperationLog('TG游戏群组列表')]

  GET    /{id}              - 群组详情
    #[GetMapping('/{id}')]
    #[Permission(code: 'tg:group:detail')]

  POST   /                  - 创建群组
    #[PostMapping('/')]
    #[Permission(code: 'tg:group:create')]
    #[OperationLog('创建TG游戏群组')]
    参数验证：tenant_id, tg_chat_id, tg_chat_title, wallet_address, bet_amount, platform_fee_rate

  PUT    /{id}              - 更新配置
    #[PutMapping('/{id}')]
    #[Permission(code: 'tg:group:update')]
    #[OperationLog('更新TG游戏群组配置')]

  DELETE /{id}              - 删除群组（软删除）
    #[DeleteMapping('/{id}')]
    #[Permission(code: 'tg:group:delete')]
    #[OperationLog('删除TG游戏群组')]

  PUT    /{id}/status       - 启用/停用
    #[PutMapping('/{id}/status')]
    #[Permission(code: 'tg:group:status')]
    #[OperationLog('变更TG游戏群组状态')]

  GET    /{id}/statistics   - 群组统计数据
    #[GetMapping('/{id}/statistics')]
    #[Permission(code: 'tg:group:statistics')]
    返回：总投注、总中奖、总玩家数、总节点数等
```

#### 2. TgSnakeGameController（游戏监控）
```php
位置：http/backend/controller/TgSnakeGameController.php
继承：BasicController
注解：#[RestController("/admin/tg-snake-game")]
注入：TgSnakeGameService, TgSnakeNodeRepository

接口列表：
  GET    /{groupId}/snake   - 当前蛇身状态
    #[GetMapping('/{groupId}/snake')]
    #[Permission(code: 'tg:game:snake')]
    返回：当前蛇身节点列表、奖池金额、蛇身长度

  GET    /{groupId}/nodes   - 节点列表（分页）
    #[GetMapping('/{groupId}/nodes')]
    #[Permission(code: 'tg:game:nodes')]
    支持筛选：status, wallet_cycle, player_address

  GET    /{groupId}/pool    - 奖池金额
    #[GetMapping('/{groupId}/pool')]
    #[Permission(code: 'tg:game:pool')]
    返回：当前奖池、历史最高奖池

  POST   /{groupId}/reset   - 手动重置游戏（管理员）
    #[PostMapping('/{groupId}/reset')]
    #[Permission(code: 'tg:game:reset')]
    #[OperationLog('手动重置TG游戏')]
    注意：需要二次确认，记录操作日志
```

#### 3. TgPrizeRecordController（中奖记录）
```php
位置：http/backend/controller/TgPrizeRecordController.php
继承：BasicController
注解：#[RestController("/admin/tg-prize-record")]
注入：TgPrizeService, TgPrizeRecordRepository

接口列表：
  GET    /list              - 中奖记录列表（分页）
    #[GetMapping('/list')]
    #[Permission(code: 'tg:prize:list')]
    支持筛选：group_id, wallet_cycle, status, date_range

  GET    /{id}              - 中奖详情
    #[GetMapping('/{id}')]
    #[Permission(code: 'tg:prize:detail')]
    返回：中奖信息、转账记录、区间节点详情

  GET    /statistics        - 中奖统计
    #[GetMapping('/statistics')]
    #[Permission(code: 'tg:prize:statistics')]
    返回：总中奖次数、总奖金、手续费收入等

  POST   /{id}/retry        - 重试失败转账
    #[PostMapping('/{id}/retry')]
    #[Permission(code: 'tg:prize:retry')]
    #[OperationLog('重试TG游戏奖金转账')]
    仅适用于status=4或5的中奖记录
```

#### 4. TgStatisticsController（数据统计）
```php
位置：http/backend/controller/TgStatisticsController.php
继承：BasicController
注解：#[RestController("/admin/tg-statistics")]
注入：TgGameGroupService, TgPrizeService, TgSnakeNodeRepository

接口列表：
  GET    /overview          - 整体概览
    #[GetMapping('/overview')]
    #[Permission(code: 'tg:statistics:overview')]
    返回：
    - 总群组数、活跃群组数
    - 总投注金额、总中奖金额
    - 总玩家数、活跃玩家数
    - 平台手续费收入
    - 今日/本周/本月数据对比

  GET    /group/{id}        - 群组维度统计
    #[GetMapping('/group/{id}')]
    #[Permission(code: 'tg:statistics:group')]
    返回：
    - 投注统计（总次数、总金额、趋势图）
    - 中奖统计（总次数、总金额、中奖率）
    - 玩家统计（总数、活跃度、排行榜）
    - 手续费统计（总收入、费率变更历史）

  GET    /tenant/{id}       - 租户维度统计
    #[GetMapping('/tenant/{id}')]
    #[Permission(code: 'tg:statistics:tenant')]
    返回：
    - 汇总所有群组数据
    - 群组排行榜

  GET    /fee               - 手续费收入统计
    #[GetMapping('/fee')]
    #[Permission(code: 'tg:statistics:fee')]
    返回：
    - 按时间维度：今日、本周、本月、自定义区间
    - 按群组维度：各群组手续费收入排行
    - 趋势图数据
```

---

### 阶段四：后台进程/定时任务

**优先级：⭐⭐⭐⭐**
**工作量：4个Process类**

#### 1. TronMonitorCrontab（TRON交易监听）⭐⭐⭐⭐⭐
```php
位置：app/process/TronMonitorCrontab.php
实现：Workerman\Timer

配置：
  频率：每3秒执行一次
  进程数：1

运行逻辑：
  public function onWorkerStart($worker) {
      Timer::add(3, function() {
          // 1. 获取所有活跃群组配置
          $configs = TgGameGroupConfigRepository->getActiveConfigs();

          foreach ($configs as $config) {
              // 2. 检查钱包变更状态（变更中不处理）
              if ($config->wallet_change_status === 2) {
                  continue;
              }

              // 3. 获取最后处理的区块高度
              $lastBlock = TgTronTransactionLogRepository->getLastProcessedBlock($config->id);

              // 4. 调用TronGrid API查询新交易
              $transactions = TronWebHelper::getTransactions(
                  $config->wallet_address,
                  $lastBlock + 1
              );

              // 5. 遍历交易并验证
              foreach ($transactions as $tx) {
                  if (TgTronMonitorService->validateTransaction($tx, $config)) {
                      // 6. 记录交易日志
                      TgTronTransactionLogRepository->create([...]);

                      // 7. 推入处理队列（延迟60秒等待确认）
                      Redis::send('tron-tx-process', [
                          'config_id' => $config->id,
                          'tx_hash' => $tx['hash']
                      ], delay: 60);
                  }
              }
          }
      });
  }

错误处理：
  - API限流：捕获异常，延迟下次执行
  - 网络超时：记录日志，继续处理下一个群组
  - 数据异常：跳过该交易，记录告警日志
```

#### 2. WalletChangeNotifierCrontab（钱包变更通知）⭐⭐⭐
```php
位置：app/process/WalletChangeNotifierCrontab.php
实现：Workerman\Timer

配置：
  频率：每10秒执行一次
  进程数：1

运行逻辑：
  public function onWorkerStart($worker) {
      Timer::add(10, function() {
          // 1. 查询所有正在变更中的群组
          $changingConfigs = TgGameGroupConfigRepository->getChangingConfigs();

          foreach ($changingConfigs as $config) {
              $now = now();
              $endTime = $config->wallet_change_end_at;

              // 2. 检查是否到期
              if ($now->gte($endTime)) {
                  // 执行钱包变更
                  TgWalletChangeService->completeWalletChange($config->id);
                  continue;
              }

              // 3. 计算剩余时间
              $remainingSeconds = $endTime->diffInSeconds($now);

              // 4. 发送倒计时提醒
              TgWalletChangeService->sendCountdownNotification(
                  $config->tg_chat_id,
                  $remainingSeconds
              );
          }
      });
  }

通知内容：
  - 剩余时间（格式：MM:SS）
  - 生效时间
  - 警告信息
```

#### 3. PrizeTransferQueueConsumer（奖金转账队列）⭐⭐⭐⭐
```php
位置：app/queue/consumer/PrizeTransferQueueConsumer.php
实现：Redis Queue Consumer

配置：
  队列名：tg-prize-transfer
  进程数：3
  超时时间：120秒

消费逻辑：
  public function consume($data) {
      $prizeRecordId = $data['prize_record_id'];

      // 1. 获取中奖记录
      $prizeRecord = TgPrizeRecordRepository->findById($prizeRecordId);
      if (!$prizeRecord || $prizeRecord->status !== 1) {
          return; // 已处理或无效
      }

      // 2. 更新状态为转账中
      $prizeRecord->update(['status' => 2]);

      // 3. 获取所有待转账记录
      $transfers = TgPrizeTransferRepository->getByPrizeRecordId($prizeRecordId);

      // 4. 获取热钱包配置
      $config = TgGameGroupConfigRepository->findById($prizeRecord->group_id);

      $successCount = 0;
      $failCount = 0;

      foreach ($transfers as $transfer) {
          try {
              // 5. 检查余额
              $balance = TgTransferService->checkHotWalletBalance($config->hot_wallet_address);
              if ($balance < $transfer->amount) {
                  // 余额不足，暂停转账，通知管理员
                  TgTelegramBotService->notifyInsufficientBalance($config->tg_chat_id);
                  break;
              }

              // 6. 执行转账
              $txHash = TgTransferService->transfer(
                  $config->hot_wallet_address,
                  $transfer->to_address,
                  $transfer->amount,
                  $config->hot_wallet_private_key
              );

              // 7. 更新转账记录
              $transfer->update([
                  'tx_hash' => $txHash,
                  'status' => 2 // processing
              ]);

              // 8. 等待确认（另一个定时任务处理）
              Redis::send('tg-transfer-confirm', [
                  'transfer_id' => $transfer->id,
                  'tx_hash' => $txHash
              ], delay: 60);

              $successCount++;

              // 9. 发送转账通知
              TgTransferService->sendTransferNotification($transfer);

          } catch (Exception $e) {
              // 记录错误
              $transfer->update([
                  'status' => 4, // failed
                  'error_message' => $e->getMessage(),
                  'retry_count' => $transfer->retry_count + 1
              ]);

              $failCount++;

              // 重试次数 < 3 则重新推入队列
              if ($transfer->retry_count < 3) {
                  Redis::send('tg-prize-transfer', [
                      'prize_record_id' => $prizeRecordId
                  ], delay: 300); // 5分钟后重试
              }
          }
      }

      // 10. 更新中奖记录状态
      if ($failCount === 0) {
          $prizeRecord->update(['status' => 3]); // completed
      } elseif ($successCount > 0) {
          $prizeRecord->update(['status' => 5]); // partial_failure
      } else {
          $prizeRecord->update(['status' => 4]); // failed
      }
  }
```

#### 4. TronTxProcessQueueConsumer（交易处理队列）⭐⭐⭐⭐⭐
```php
位置：app/queue/consumer/TronTxProcessQueueConsumer.php
实现：Redis Queue Consumer

配置：
  队列名：tron-tx-process
  进程数：5
  超时时间：60秒

消费逻辑：
  public function consume($data) {
      $configId = $data['config_id'];
      $txHash = $data['tx_hash'];

      // 1. 检查交易是否已处理
      $log = TgTronTransactionLogRepository->findByTxHash($txHash);
      if ($log->processed) {
          return; // 已处理，跳过
      }

      // 2. 获取群组配置
      $config = TgGameGroupConfigRepository->findById($configId);
      $group = TgGameGroupRepository->getByConfigId($configId);

      // 3. 获取交易详情
      $tx = TronWebHelper::getTransactionByHash($txHash);

      // 4. 再次验证交易（确保19个区块已确认）
      if (!TgTronMonitorService->validateTransaction($tx, $config)) {
          $log->update(['is_valid' => 0, 'invalid_reason' => '二次验证失败']);
          return;
      }

      // 5. 提取购彩凭证
      $ticketNumber = TgSnakeGameService->extractTicketNumber($txHash);

      // 6. 生成流水号
      $ticketSerialNo = TgSnakeGameService->generateTicketSerialNo($group->id);

      // 7. 查询钱包绑定信息
      $walletBinding = TgPlayerWalletBindingRepository->getByWalletAddress(
          $group->id,
          $tx['from']
      );

      // 8. 创建蛇身节点
      $node = TgSnakeNodeRepository->create([
          'group_id' => $group->id,
          'wallet_cycle' => $config->wallet_change_count,
          'ticket_number' => $ticketNumber,
          'ticket_serial_no' => $ticketSerialNo,
          'player_address' => $tx['from'],
          'player_tg_username' => $walletBinding->tg_username ?? null,
          'player_tg_user_id' => $walletBinding->tg_user_id ?? null,
          'amount' => $tx['amount'],
          'tx_hash' => $txHash,
          'block_height' => $tx['block_height'],
          'daily_sequence' => TgSnakeNodeRepository->countDailyNodes($group->id) + 1,
          'status' => 1 // active
      ]);

      // 9. 推送购彩成功通知
      TgTelegramBotService->sendPurchaseNotification($group->tg_chat_id, $node, $walletBinding);

      // 10. 检查中奖匹配
      $matchResult = TgSnakeGameService->checkMatch($group->id, $node);

      if ($matchResult) {
          // 触发中奖处理
          TgPrizeService->handlePrize($group->id, $matchResult);
      }

      // 11. 标记交易已处理
      $log->update(['processed' => 1]);
  }
```

---

### 阶段五：常量管理和多语言适配 ⭐⭐⭐⭐⭐

**优先级：⭐⭐⭐⭐⭐（必须完成）**
**工作量：2个Constants类 + 4个翻译文件**

#### 重要说明
根据项目现有规范，所有状态常量必须：
1. 使用 `ConstantsOptionTrait` trait
2. 常量值映射到多语言翻译key
3. 支持中英文双语

---

#### 1. TgGameGroup常量类
```php
位置：app/constants/TgGameGroup.php
继承：use ConstantsOptionTrait

常量定义：
  // 群组状态
  const STATUS_NORMAL = 1;      // 正常
  const STATUS_DISABLED = 0;    // 停用

  public static array $status_list = [
      self::STATUS_NORMAL   => 'tg_game_group.enums.status.1',
      self::STATUS_DISABLED => 'tg_game_group.enums.status.0',
  ];

  // 钱包变更状态
  const WALLET_CHANGE_STATUS_NORMAL = 1;    // 正常
  const WALLET_CHANGE_STATUS_CHANGING = 2;  // 变更中

  public static array $wallet_change_status_list = [
      self::WALLET_CHANGE_STATUS_NORMAL   => 'tg_game_group.enums.wallet_change_status.1',
      self::WALLET_CHANGE_STATUS_CHANGING => 'tg_game_group.enums.wallet_change_status.2',
  ];
```

---

#### 2. TgSnakeNode常量类
```php
位置：app/constants/TgSnakeNode.php
继承：use ConstantsOptionTrait

常量定义：
  // 节点状态
  const STATUS_ACTIVE = 1;      // 活跃
  const STATUS_MATCHED = 2;     // 已中奖
  const STATUS_CANCELLED = 3;   // 未中奖（已取消）
  const STATUS_ARCHIVED = 4;    // 已归档

  public static array $status_list = [
      self::STATUS_ACTIVE    => 'tg_snake_node.enums.status.1',
      self::STATUS_MATCHED   => 'tg_snake_node.enums.status.2',
      self::STATUS_CANCELLED => 'tg_snake_node.enums.status.3',
      self::STATUS_ARCHIVED  => 'tg_snake_node.enums.status.4',
  ];
```

---

#### 3. TgPrizeRecord常量类
```php
位置：app/constants/TgPrizeRecord.php
继承：use ConstantsOptionTrait

常量定义：
  // 中奖状态
  const STATUS_PENDING = 1;         // 待处理
  const STATUS_TRANSFERRING = 2;    // 转账中
  const STATUS_COMPLETED = 3;       // 已完成
  const STATUS_FAILED = 4;          // 失败
  const STATUS_PARTIAL_FAILED = 5;  // 部分失败

  public static array $status_list = [
      self::STATUS_PENDING        => 'tg_prize_record.enums.status.1',
      self::STATUS_TRANSFERRING   => 'tg_prize_record.enums.status.2',
      self::STATUS_COMPLETED      => 'tg_prize_record.enums.status.3',
      self::STATUS_FAILED         => 'tg_prize_record.enums.status.4',
      self::STATUS_PARTIAL_FAILED => 'tg_prize_record.enums.status.5',
  ];

  // 中奖类型
  const PRIZE_TYPE_JACKPOT = 1;  // 连号清空奖池
  const PRIZE_TYPE_RANGE = 2;    // 区间匹配

  public static array $prize_type_list = [
      self::PRIZE_TYPE_JACKPOT => 'tg_prize_record.enums.prize_type.1',
      self::PRIZE_TYPE_RANGE   => 'tg_prize_record.enums.prize_type.2',
  ];
```

---

#### 4. TgPrizeTransfer常量类
```php
位置：app/constants/TgPrizeTransfer.php
继承：use ConstantsOptionTrait

常量定义：
  // 转账状态
  const STATUS_PENDING = 1;     // 待转账
  const STATUS_PROCESSING = 2;  // 处理中
  const STATUS_SUCCESS = 3;     // 成功
  const STATUS_FAILED = 4;      // 失败

  public static array $status_list = [
      self::STATUS_PENDING    => 'tg_prize_transfer.enums.status.1',
      self::STATUS_PROCESSING => 'tg_prize_transfer.enums.status.2',
      self::STATUS_SUCCESS    => 'tg_prize_transfer.enums.status.3',
      self::STATUS_FAILED     => 'tg_prize_transfer.enums.status.4',
  ];
```

---

#### 5. 多语言翻译文件

##### 5.1 中文翻译
```php
位置：resource/translations/zh_CN/tg_game_group.php

return [
    'enums' => [
        'status' => [
            0 => '已停用',
            1 => '正常',
        ],
        'wallet_change_status' => [
            1 => '正常',
            2 => '变更中',
        ],
    ],
];
```

```php
位置：resource/translations/zh_CN/tg_snake_node.php

return [
    'enums' => [
        'status' => [
            1 => '活跃',
            2 => '已中奖',
            3 => '已取消',
            4 => '已归档',
        ],
    ],
];
```

```php
位置：resource/translations/zh_CN/tg_prize_record.php

return [
    'enums' => [
        'status' => [
            1 => '待处理',
            2 => '转账中',
            3 => '已完成',
            4 => '失败',
            5 => '部分失败',
        ],
        'prize_type' => [
            1 => '连号大奖',
            2 => '区间中奖',
        ],
    ],
];
```

```php
位置：resource/translations/zh_CN/tg_prize_transfer.php

return [
    'enums' => [
        'status' => [
            1 => '待转账',
            2 => '处理中',
            3 => '成功',
            4 => '失败',
        ],
    ],
];
```

##### 5.2 英文翻译
```php
位置：resource/translations/en/tg_game_group.php

return [
    'enums' => [
        'status' => [
            0 => 'Disabled',
            1 => 'Active',
        ],
        'wallet_change_status' => [
            1 => 'Normal',
            2 => 'Changing',
        ],
    ],
];
```

```php
位置：resource/translations/en/tg_snake_node.php

return [
    'enums' => [
        'status' => [
            1 => 'Active',
            2 => 'Matched',
            3 => 'Cancelled',
            4 => 'Archived',
        ],
    ],
];
```

```php
位置：resource/translations/en/tg_prize_record.php

return [
    'enums' => [
        'status' => [
            1 => 'Pending',
            2 => 'Transferring',
            3 => 'Completed',
            4 => 'Failed',
            5 => 'Partial Failed',
        ],
        'prize_type' => [
            1 => 'Jackpot',
            2 => 'Range Match',
        ],
    ],
];
```

```php
位置：resource/translations/en/tg_prize_transfer.php

return [
    'enums' => [
        'status' => [
            1 => 'Pending',
            2 => 'Processing',
            3 => 'Success',
            4 => 'Failed',
        ],
    ],
];
```

---

#### 6. 使用示例

```php
// 在Controller或Service中使用
use app\constants\TgPrizeRecord;

// 获取状态文本（自动根据当前语言环境）
$statusText = TgPrizeRecord::getStatusText(TgPrizeRecord::STATUS_COMPLETED);
// 中文环境返回：已完成
// 英文环境返回：Completed

// 获取所有状态选项（用于下拉框）
$statusOptions = TgPrizeRecord::getStatusOptions();
// 返回：[1 => '待处理', 2 => '转账中', ...]

// 获取中奖类型文本
$prizeTypeText = TgPrizeRecord::getPrizeTypeText(TgPrizeRecord::PRIZE_TYPE_JACKPOT);
// 返回：连号大奖 / Jackpot
```

---

### 阶段六：Bot服务改造 ⭐⭐⭐⭐⭐

**优先级：⭐⭐⭐⭐⭐（核心功能）**
**工作量：重构CommandEnum + 重构TelegramCommandService + 新增TgBotCommandService**

#### 重要说明
1. **删除原有订单交易相关指令**：清除所有支付订单查询、创建、统计等命令
2. **保留交互逻辑和流程**：保留CommandEnum的基础架构和TelegramService的消息处理逻辑
3. **创建TG贪吃蛇群指令**：实现游戏相关的所有命令

---

#### 1. 重构CommandEnum（清理旧指令，添加游戏指令）

```php
位置：app/service/bot/CommandEnum.php

改造内容：

  // ❌ 删除以下旧指令
  - 'query'               => 'Query'
  - 'order'               => 'Order'
  - 'query_collect_order' => 'QueryCollectOrder'
  - 'query_pay_order'     => 'QueryPayOrder'
  - 'create_pay_order'    => 'CreatePayOrder'
  - 'submit_utr'          => 'SubmitUtr'
  - 'count_collect_order' => 'CountCollectOrder'
  - 'count_pay_order'     => 'CountPayOrder'

  // ✅ 保留基础指令
  - 'help'          => 'Help'
  - 'get_id'        => 'GetId'
  - 'get_group_id'  => 'GetGroupId'
  - 'bind'          => 'Bind'  // 改造为绑定TG游戏群组

  // ✅ 新增TG游戏指令（英文）
  public const COMMAND_SET = [
      // 基础指令
      'help'                => 'Help',
      'get_id'              => 'GetId',
      'get_group_id'        => 'GetGroupId',

      // 群组管理指令（仅管理员）
      'bind'                => 'Bind',              // 绑定群组到租户
      'wallet'              => 'Wallet',            // 设置/更新钱包地址
      'cancelwallet'        => 'CancelWallet',      // 取消钱包变更
      'setbet'              => 'SetBet',            // 设置投注金额
      'setfee'              => 'SetFee',            // 设置手续费比例
      'info'                => 'Info',              // 查看群组配置

      // 游戏查询指令（所有用户）
      'address'             => 'Address',           // 查看收款地址
      'snake'               => 'Snake',             // 查看当前蛇身
      'mytickets'           => 'MyTickets',         // 查看我的购彩记录
      'ticket'              => 'Ticket',            // 查询指定流水号
      'stats'               => 'Stats',             // 查看游戏统计
      'history'             => 'History',           // 查看历史中奖
      'rules'               => 'Rules',             // 查看游戏规则

      // 钱包绑定指令（所有用户）
      'bindwallet'          => 'BindWallet',        // 绑定个人钱包
      'unbindwallet'        => 'UnbindWallet',      // 解绑钱包
      'mywallet'            => 'MyWallet',          // 查看绑定钱包
  ];

  // 命令描述（英文）
  public static array $commandDescMap = [
      'help'          => "<blockquote>[Eg] /help</blockquote>",
      'get_id'        => "<blockquote>[Eg] /get_id</blockquote>",
      'get_group_id'  => "<blockquote>[Eg] /get_group_id</blockquote>",
      'bind'          => "<blockquote>[Eg] /bind 000001" . PHP_EOL . "[Param] tenant_id !Tenant ID</blockquote>",
      'wallet'        => "<blockquote>[Eg] /wallet TXYZa2kR6..." . PHP_EOL . "[Param] address !TRON Wallet Address</blockquote>",
      'cancelwallet'  => "<blockquote>[Eg] /cancelwallet</blockquote>",
      'setbet'        => "<blockquote>[Eg] /setbet 5" . PHP_EOL . "[Param] amount !Bet Amount (TRX)</blockquote>",
      'setfee'        => "<blockquote>[Eg] /setfee 10" . PHP_EOL . "[Param] rate !Platform Fee Rate (%)</blockquote>",
      'info'          => "<blockquote>[Eg] /info</blockquote>",
      'address'       => "<blockquote>[Eg] /address</blockquote>",
      'snake'         => "<blockquote>[Eg] /snake</blockquote>",
      'mytickets'     => "<blockquote>[Eg] /mytickets</blockquote>",
      'ticket'        => "<blockquote>[Eg] /ticket 20250108-001" . PHP_EOL . "[Param] serial_no !Ticket Serial Number</blockquote>",
      'stats'         => "<blockquote>[Eg] /stats</blockquote>",
      'history'       => "<blockquote>[Eg] /history</blockquote>",
      'rules'         => "<blockquote>[Eg] /rules</blockquote>",
      'bindwallet'    => "<blockquote>[Eg] /bindwallet TXYZa2kR6..." . PHP_EOL . "[Param] address !Your TRON Wallet Address</blockquote>",
      'unbindwallet'  => "<blockquote>[Eg] /unbindwallet</blockquote>",
      'mywallet'      => "<blockquote>[Eg] /mywallet</blockquote>",
  ];

  // ✅ 新增TG游戏指令（中文）
  public const COMMAND_SET_CN = [
      // 基础指令
      '帮助'         => 'cnHelp',
      '获取ID'       => 'cnGetId',
      '获取群ID'     => 'cnGetGroupId',

      // 群组管理指令
      '绑定'         => 'cnBind',
      '钱包'         => 'cnWallet',
      '取消钱包'     => 'cnCancelWallet',
      '设置投注'     => 'cnSetBet',
      '设置费率'     => 'cnSetFee',
      '群组信息'     => 'cnInfo',

      // 游戏查询指令
      '收款地址'     => 'cnAddress',
      '蛇身'         => 'cnSnake',
      '我的购彩'     => 'cnMyTickets',
      '查询凭证'     => 'cnTicket',
      '统计'         => 'cnStats',
      '历史中奖'     => 'cnHistory',
      '游戏规则'     => 'cnRules',

      // 钱包绑定指令
      '绑定钱包'     => 'cnBindWallet',
      '解绑钱包'     => 'cnUnbindWallet',
      '我的钱包'     => 'cnMyWallet',
  ];

  // 命令描述（中文）
  public static array $commandDescCnMap = [
      '帮助'         => "<blockquote>[示例] /帮助</blockquote>",
      '获取ID'       => "<blockquote>[示例] /获取ID</blockquote>",
      '获取群ID'     => "<blockquote>[示例] /获取群ID</blockquote>",
      '绑定'         => "<blockquote>[示例] /绑定 000001" . PHP_EOL . "[参数] tenant_id !租户ID</blockquote>",
      '钱包'         => "<blockquote>[示例] /钱包 TXYZa2kR6..." . PHP_EOL . "[参数] address !TRON钱包地址</blockquote>",
      '取消钱包'     => "<blockquote>[示例] /取消钱包</blockquote>",
      '设置投注'     => "<blockquote>[示例] /设置投注 5" . PHP_EOL . "[参数] amount !投注金额(TRX)</blockquote>",
      '设置费率'     => "<blockquote>[示例] /设置费率 10" . PHP_EOL . "[参数] rate !平台手续费比例(%)</blockquote>",
      '群组信息'     => "<blockquote>[示例] /群组信息</blockquote>",
      '收款地址'     => "<blockquote>[示例] /收款地址</blockquote>",
      '蛇身'         => "<blockquote>[示例] /蛇身</blockquote>",
      '我的购彩'     => "<blockquote>[示例] /我的购彩</blockquote>",
      '查询凭证'     => "<blockquote>[示例] /查询凭证 20250108-001" . PHP_EOL . "[参数] serial_no !凭证流水号</blockquote>",
      '统计'         => "<blockquote>[示例] /统计</blockquote>",
      '历史中奖'     => "<blockquote>[示例] /历史中奖</blockquote>",
      '游戏规则'     => "<blockquote>[示例] /游戏规则</blockquote>",
      '绑定钱包'     => "<blockquote>[示例] /绑定钱包 TXYZa2kR6..." . PHP_EOL . "[参数] address !您的TRON钱包地址</blockquote>",
      '解绑钱包'     => "<blockquote>[示例] /解绑钱包</blockquote>",
      '我的钱包'     => "<blockquote>[示例] /我的钱包</blockquote>",
  ];
```

---

#### 2. 重构TelegramCommandService（删除订单逻辑）

```php
位置：app/service/bot/TelegramCommandService.php

改造内容：

  // ❌ 删除以下依赖注入
  - CollectionOrderService
  - DisbursementOrderService
  - TransactionVoucherRepository

  // ❌ 删除以下方法
  - writeOffOrderByPhoto()  // 图片补单
  - 所有订单查询相关方法
  - 所有订单创建相关方法
  - 所有订单统计相关方法

  // ✅ 保留基础方法
  - getTenant()             // 获取租户（改造为获取游戏群组配置）
  - getFileUrl()            // 获取文件下载地址（可能用于上传凭证截图）

  // ✅ 新增依赖注入
  #[Inject]
  protected TgGameGroupConfigRepository $tgGameGroupConfigRepository;

  #[Inject]
  protected TgSnakeNodeRepository $tgSnakeNodeRepository;

  #[Inject]
  protected TgPlayerWalletBindingRepository $tgPlayerWalletBindingRepository;

  // ✅ 改造getTenant为getGameGroupConfig
  private function getGameGroupConfig(): ?\app\model\ModelTgGameGroupConfig
  {
      $chatID = $this->telegramBot->ChatID();
      return $this->tgGameGroupConfigRepository->getByTgChatId($chatID);
  }
```

---

#### 3. 新增TgBotCommandService（游戏命令处理）

```php
位置：app/service/bot/TgBotCommandService.php

功能：处理所有TG贪吃蛇游戏相关的命令

依赖注入：
  #[Inject]
  protected TgGameGroupConfigRepository $configRepository;

  #[Inject]
  protected TgSnakeNodeRepository $nodeRepository;

  #[Inject]
  protected TgPrizeRecordRepository $prizeRepository;

  #[Inject]
  protected TgPlayerWalletBindingRepository $walletBindingRepository;

  #[Inject]
  protected TgWalletChangeService $walletChangeService;

  #[Inject]
  protected TgPlayerWalletService $playerWalletService;

核心方法：

  // 基础指令
  public function handleHelp(): string
  public function handleGetId(): string
  public function handleGetGroupId(): string

  // 群组管理指令（验证管理员权限）
  public function handleBind(string $tenantId): string
  public function handleWallet(string $address): string
  public function handleCancelWallet(): string
  public function handleSetBet(float $amount): string
  public function handleSetFee(float $rate): string
  public function handleInfo(): string

  // 游戏查询指令
  public function handleAddress(): string
  public function handleSnake(): string
  public function handleMyTickets(int $userId): string
  public function handleTicket(string $serialNo): string
  public function handleStats(): string
  public function handleHistory(): string
  public function handleRules(): string

  // 钱包绑定指令
  public function handleBindWallet(int $userId, string $username, string $address): string
  public function handleUnbindWallet(int $userId): string
  public function handleMyWallet(int $userId): string

  // 辅助方法
  private function checkIsAdmin(int $userId, int $chatId): bool
  private function formatSnakeChain(array $nodes): string
  private function formatTicketList(array $tickets): string
```

---

#### 4. 命令路由映射（在TelegramService中调用）

```php
// 在TelegramService的handleUpdate方法中

use app\service\bot\TgBotCommandService;

public function handleUpdate(array $update): void
{
    $command = CommandEnum::getCommand($commandText);

    // 路由到TgBotCommandService
    $botCommandService = new TgBotCommandService();

    switch ($command) {
        case 'Help':
        case 'cnHelp':
            $reply = $botCommandService->handleHelp();
            break;

        case 'Bind':
        case 'cnBind':
            $reply = $botCommandService->handleBind($params[0]);
            break;

        case 'Wallet':
        case 'cnWallet':
            $reply = $botCommandService->handleWallet($params[0]);
            break;

        case 'Snake':
        case 'cnSnake':
            $reply = $botCommandService->handleSnake();
            break;

        case 'MyTickets':
        case 'cnMyTickets':
            $reply = $botCommandService->handleMyTickets($userId);
            break;

        // ... 其他命令映射
    }

    $this->telegramBot->sendMessage($reply);
}
```

---

#### 5. 消息模板示例

```php
// 在TgBotCommandService中定义消息模板

private function getHelpMessage(): string
{
    return <<<EOT
***** TG贪吃蛇游戏帮助 *****

📋 群组管理（仅管理员）
/bind <tenant_id> - 绑定群组到租户
/wallet <address> - 设置钱包地址
/cancelwallet - 取消钱包变更
/setbet <amount> - 设置投注金额
/setfee <rate> - 设置手续费比例
/info - 查看群组配置

🎮 游戏查询
/address - 查看收款地址
/snake - 查看当前蛇身
/mytickets - 我的购彩记录
/ticket <serial_no> - 查询指定凭证
/stats - 游戏统计
/history - 历史中奖
/rules - 游戏规则

💰 钱包管理
/bindwallet <address> - 绑定钱包
/unbindwallet - 解绑钱包
/mywallet - 查看绑定信息
EOT;
}

private function getSnakeMessage(array $nodes): string
{
    $chain = $this->formatSnakeChain($nodes);
    $poolAmount = array_sum(array_column($nodes, 'amount'));

    return <<<EOT
🐍 当前蛇身状态

蛇身：{$chain}
长度：{count($nodes)} 节
奖池：{$poolAmount} TRX

继续转账参与游戏，匹配相同数字即可中奖！
使用 /address 查看收款地址
EOT;
}

private function getAddressMessage(ModelTgGameGroupConfig $config): string
{
    return <<<EOT
💰 群组收款地址

当前收款钱包地址：
{$config->wallet_address}

━━━━━━━━━━━━━━━━━━━━
📋 游戏信息：
• 投注金额：{$config->bet_amount} TRX
• 平台手续费：{$config->platform_fee_rate * 100}%
• 钱包周期：第 {$config->wallet_change_count} 期

━━━━━━━━━━━━━━━━━━━━
💡 使用说明：
1. 向上述地址转账 {$config->bet_amount} TRX 参与游戏
2. 转账成功后系统自动生成购彩凭证
3. 请勿向其他地址转账，否则无法参与游戏

使用 /help 查看游戏规则
使用 /snake 查看当前蛇身状态
EOT;
}
```

---

### 阶段七：辅助类/工具类

**优先级：⭐⭐⭐**
**工作量：3个类**

#### 1. TronWebHelper
```php
位置：app/lib/tron/TronWebHelper.php

功能：
  ✅ 交易查询
    - getTransactionByHash(string $hash): ?array
      调用TronGrid API: /wallet/gettransactionbyid
      返回：交易详情（from, to, amount, status, block_height等）

    - getTransactions(string $address, int $minBlock = 0): array
      调用TronGrid API: /v1/accounts/{address}/transactions
      参数：min_block_timestamp, only_confirmed=true
      返回：交易列表

  ✅ 转账
    - transfer(string $from, string $to, float $amount, string $privateKey): string
      使用TronWeb SDK执行转账
      参数：金额单位为TRX（自动转换为SUN）
      返回：交易哈希

  ✅ 地址验证
    - validateAddress(string $address): bool
      验证TRON地址格式
      规则：以T开头，34位字符，Base58编码

  ✅ 余额查询
    - getBalance(string $address): float
      查询钱包余额（TRX）

  ✅ 区块查询
    - getCurrentBlockNumber(): int
      获取当前区块高度

  ✅ 单位转换
    - trxToSun(float $trx): int
      TRX转SUN（1 TRX = 1,000,000 SUN）
    - sunToTrx(int $sun): float
      SUN转TRX

依赖：
  - TronWeb PHP SDK (IEXBase/tron-api)
  - 配置项：TRON_GRID_API_KEY, TRON_NODE_URL
```

#### 2. TelegramBotHelper
```php
位置：app/lib/telegram/TelegramBotHelper.php

功能：
  ✅ 消息发送
    - sendMessage(int $chatId, string $text, array $options = []): bool
      调用Telegram Bot API: sendMessage
      参数：
      - parse_mode: Markdown
      - disable_web_page_preview: true
      返回：是否发送成功

    - sendPrivateMessage(int $userId, string $text): bool
      发送私聊消息

  ✅ 命令解析
    - parseCommand(string $text): ?array
      解析命令格式：/command param1 param2
      返回：['command' => 'start', 'params' => [...]]

  ✅ Webhook处理
    - setWebhook(string $url): bool
      设置Webhook URL

    - deleteWebhook(): bool
      删除Webhook

  ✅ 消息格式化
    - formatMessage(string $template, array $data): string
      使用占位符替换
      示例：formatMessage('您好，{username}！', ['username' => 'Alice'])

    - escapeMarkdown(string $text): string
      转义Markdown特殊字符

  ✅ 用户信息
    - getMe(): array
      获取Bot信息

    - getChatMember(int $chatId, int $userId): array
      获取群组成员信息（用于验证管理员权限）

依赖：
  - Telegram Bot API
  - 配置项：TELEGRAM_BOT_TOKEN
```

#### 3. TgConstants（常量定义）
```php
位置：app/constants/TgConstants.php
继承：app\lib\traits\ConstantsTrait

常量定义：
  ✅ 游戏状态
    const STATUS_NORMAL = 1;    // 正常
    const STATUS_DISABLED = 0;  // 停用

  ✅ 节点状态
    const NODE_STATUS_ACTIVE = 1;    // 活跃
    const NODE_STATUS_MATCHED = 2;   // 已中奖
    const NODE_STATUS_CANCELLED = 3; // 未中奖
    const NODE_STATUS_ARCHIVED = 4;  // 已归档

  ✅ 中奖状态
    const PRIZE_STATUS_PENDING = 1;         // 待处理
    const PRIZE_STATUS_TRANSFERRING = 2;    // 转账中
    const PRIZE_STATUS_COMPLETED = 3;       // 已完成
    const PRIZE_STATUS_FAILED = 4;          // 失败
    const PRIZE_STATUS_PARTIAL_FAILED = 5;  // 部分失败

  ✅ 转账状态
    const TRANSFER_STATUS_PENDING = 1;     // 待转账
    const TRANSFER_STATUS_PROCESSING = 2;  // 处理中
    const TRANSFER_STATUS_SUCCESS = 3;     // 成功
    const TRANSFER_STATUS_FAILED = 4;      // 失败

  ✅ 钱包变更状态
    const WALLET_CHANGE_STATUS_NORMAL = 1;    // 正常
    const WALLET_CHANGE_STATUS_CHANGING = 2;  // 变更中

  ✅ 中奖类型
    const PRIZE_TYPE_JACKPOT = 1;  // 连号清空奖池
    const PRIZE_TYPE_RANGE = 2;    // 区间匹配

  ✅ Telegram命令
    const CMD_START = 'start';
    const CMD_HELP = 'help';
    const CMD_BIND = 'bind';
    const CMD_WALLET = 'wallet';
    const CMD_CANCEL_WALLET = 'cancelwallet';
    const CMD_SET_BET = 'setbet';
    const CMD_SET_FEE = 'setfee';
    const CMD_INFO = 'info';
    const CMD_ADDRESS = 'address';
    const CMD_BIND_WALLET = 'bindwallet';
    const CMD_UNBIND_WALLET = 'unbindwallet';
    const CMD_MY_WALLET = 'mywallet';
    const CMD_SNAKE = 'snake';
    const CMD_MY_TICKETS = 'mytickets';
    const CMD_TICKET = 'ticket';
    const CMD_STATS = 'stats';
    const CMD_HISTORY = 'history';
    const CMD_RULES = 'rules';

  ✅ 配置项
    const DEFAULT_BET_AMOUNT = 5.0;        // 默认投注金额（TRX）
    const DEFAULT_FEE_RATE = 0.1;          // 默认手续费比例（10%）
    const BLOCK_CONFIRMATION_COUNT = 19;   // 区块确认数
    const WALLET_CHANGE_COOLDOWN = 600;    // 钱包变更冷却期（秒）
    const MAX_TRANSFER_RETRY = 3;          // 最大转账重试次数
```

---

### 阶段六：事件系统

**优先级：⭐⭐⭐**
**工作量：3个Event类**

#### 1. TgPrizeRecordEvent
```php
位置：app/event/TgPrizeRecordEvent.php

事件监听：
  - tg.prize.created   → Created()
  - tg.prize.completed → Completed()
  - tg.prize.failed    → Failed()

处理逻辑：
  public static function Created($prizeRecord) {
      // 1. 记录操作日志
      OperationLogService->log([
          'module' => 'TG游戏',
          'action' => '中奖记录创建',
          'content' => "群组{$prizeRecord->group_id}产生中奖，奖金{$prizeRecord->prize_amount}"
      ]);

      // 2. 更新群组统计数据
      TgGameGroupRepository->incrementPrizeCount($prizeRecord->group_id);

      // 3. 推入转账队列
      Redis::send('tg-prize-transfer', [
          'prize_record_id' => $prizeRecord->id
      ]);
  }

  public static function Completed($prizeRecord) {
      // 1. 记录操作日志
      OperationLogService->log([
          'module' => 'TG游戏',
          'action' => '中奖转账完成',
          'content' => "中奖记录{$prizeRecord->id}转账完成"
      ]);

      // 2. 更新统计数据
      TgGameGroupRepository->updatePrizeStatistics($prizeRecord->group_id);
  }

  public static function Failed($prizeRecord) {
      // 1. 发送告警通知
      TelegramBotHelper->sendMessage(
          $config->tg_chat_id,
          "⚠️ 中奖转账失败，请联系管理员处理！\n中奖记录ID：{$prizeRecord->id}"
      );

      // 2. 记录错误日志
      Log::error('TG游戏中奖转账失败', [
          'prize_record_id' => $prizeRecord->id,
          'group_id' => $prizeRecord->group_id
      ]);
  }
```

#### 2. TgWalletChangeEvent
```php
位置：app/event/TgWalletChangeEvent.php

事件监听：
  - tg.wallet.change.started   → Started()
  - tg.wallet.change.cancelled → Cancelled()
  - tg.wallet.change.completed → Completed()

处理逻辑：
  public static function Started($config) {
      // 1. 记录配置变更日志
      TgGameGroupConfigLogRepository->create([
          'config_id' => $config->id,
          'field' => 'wallet_address',
          'old_value' => $config->wallet_address,
          'new_value' => $config->pending_wallet_address,
          'change_type' => 'started',
          'operator' => 'admin'
      ]);

      // 2. 发送开始变更通知
      TgTelegramBotService->sendWalletChangeStartNotification($config);
  }

  public static function Cancelled($config) {
      // 1. 记录取消日志
      TgGameGroupConfigLogRepository->create([
          'config_id' => $config->id,
          'field' => 'wallet_address',
          'change_type' => 'cancelled',
          'operator' => 'admin'
      ]);

      // 2. 发送取消通知
      TgTelegramBotService->sendWalletChangeCancelNotification($config);
  }

  public static function Completed($config, $archivedData) {
      // 1. 记录完成日志
      TgGameGroupConfigLogRepository->create([
          'config_id' => $config->id,
          'field' => 'wallet_address',
          'old_value' => $archivedData['old_address'],
          'new_value' => $config->wallet_address,
          'change_type' => 'completed',
          'extra_data' => json_encode($archivedData),
          'operator' => 'system'
      ]);

      // 2. 发送完成通知
      TgTelegramBotService->sendWalletChangeCompletedNotification(
          $config,
          $archivedData
      );

      // 3. 清理旧周期数据（可选，定期任务）
      // ...
  }
```

#### 3. TgSnakeNodeEvent
```php
位置：app/event/TgSnakeNodeEvent.php

事件监听：
  - tg.node.created  → Created()
  - tg.node.matched  → Matched()
  - tg.node.archived → Archived()

处理逻辑：
  public static function Created($node) {
      // 1. 更新群组蛇身状态
      $group = TgGameGroupRepository->findByConfigId($node->group_id);

      // 获取当前活跃节点ID列表
      $activeNodeIds = TgSnakeNodeRepository->getActiveNodeIds($node->group_id);

      $group->update([
          'current_snake_nodes' => implode(',', $activeNodeIds),
          'prize_pool_amount' => array_sum(array_column($activeNodes, 'amount'))
      ]);

      // 2. 推送蛇身拼接通知
      TgTelegramBotService->sendSnakeAppendNotification($group, $node);
  }

  public static function Matched($node, $prizeRecord) {
      // 1. 标记节点为已中奖
      $node->update([
          'status' => 2,
          'matched_prize_id' => $prizeRecord->id
      ]);

      // 2. 更新群组最后中奖信息
      $group = TgGameGroupRepository->findByConfigId($node->group_id);
      $group->update([
          'last_prize_serial_no' => $prizeRecord->prize_serial_no,
          'last_prize_amount' => $prizeRecord->prize_amount,
          'last_prize_at' => now()
      ]);
  }

  public static function Archived($nodeIds, $groupId) {
      // 1. 批量归档节点
      TgSnakeNodeRepository->archiveNodes($nodeIds);

      // 2. 清空群组蛇身
      $group = TgGameGroupRepository->findByConfigId($groupId);
      $group->update([
          'last_snake_nodes' => $group->current_snake_nodes,
          'current_snake_nodes' => '',
          'prize_pool_amount' => 0
      ]);
  }
```

---

## 四、配置文件调整

### 4.1 进程配置
```php
// config/process.php

return [
    // 原有进程...

    // 新增：TRON交易监听进程
    'tron-monitor' => [
        'handler'  => app\process\TronMonitorCrontab::class,
        'count'    => 1,  // 单进程即可
        'reloadable' => true,
    ],

    // 新增：钱包变更通知进程
    'wallet-change-notifier' => [
        'handler'  => app\process\WalletChangeNotifierCrontab::class,
        'count'    => 1,  // 单进程即可
        'reloadable' => true,
    ],

    // 新增：交易处理队列消费者
    'tron-tx-process-consumer' => [
        'handler'  => app\queue\consumer\TronTxProcessQueueConsumer::class,
        'count'    => 5,  // 5个进程并发处理
        'reloadable' => true,
    ],

    // 新增：奖金转账队列消费者
    'prize-transfer-consumer' => [
        'handler'  => app\queue\consumer\PrizeTransferQueueConsumer::class,
        'count'    => 3,  // 3个进程并发处理
        'reloadable' => true,
    ],
];
```

### 4.2 事件配置
```php
// config/event.php

return [
    // 原有事件...

    // 新增：TG游戏中奖事件
    'tg.prize.created'   => [app\event\TgPrizeRecordEvent::class, 'Created'],
    'tg.prize.completed' => [app\event\TgPrizeRecordEvent::class, 'Completed'],
    'tg.prize.failed'    => [app\event\TgPrizeRecordEvent::class, 'Failed'],

    // 新增：钱包变更事件
    'tg.wallet.change.started'   => [app\event\TgWalletChangeEvent::class, 'Started'],
    'tg.wallet.change.cancelled' => [app\event\TgWalletChangeEvent::class, 'Cancelled'],
    'tg.wallet.change.completed' => [app\event\TgWalletChangeEvent::class, 'Completed'],

    // 新增：蛇身节点事件
    'tg.node.created'  => [app\event\TgSnakeNodeEvent::class, 'Created'],
    'tg.node.matched'  => [app\event\TgSnakeNodeEvent::class, 'Matched'],
    'tg.node.archived' => [app\event\TgSnakeNodeEvent::class, 'Archived'],
];
```

### 4.3 TRON配置（新增）
```php
// config/tron.php

return [
    // TronGrid API配置
    'tron_grid' => [
        'api_url' => env('TRON_GRID_API_URL', 'https://api.trongrid.io'),
        'api_key' => env('TRON_GRID_API_KEY', ''),
    ],

    // TRON节点配置
    'tron_node' => [
        'full_node' => env('TRON_FULL_NODE', 'https://api.trongrid.io'),
        'solidity_node' => env('TRON_SOLIDITY_NODE', 'https://api.trongrid.io'),
        'event_server' => env('TRON_EVENT_SERVER', 'https://api.trongrid.io'),
    ],

    // 区块确认配置
    'block_confirmation' => env('TRON_BLOCK_CONFIRMATION', 19),

    // 监听配置
    'monitor' => [
        'interval' => 3,  // 轮询间隔（秒）
        'batch_size' => 50,  // 每次查询交易数量
    ],
];
```

### 4.4 Telegram Bot配置（新增）
```php
// config/telegram.php

return [
    // Bot Token
    'bot_token' => env('TELEGRAM_BOT_TOKEN', ''),

    // Webhook配置
    'webhook' => [
        'enabled' => env('TELEGRAM_WEBHOOK_ENABLED', false),
        'url' => env('TELEGRAM_WEBHOOK_URL', ''),
    ],

    // 消息配置
    'message' => [
        'parse_mode' => 'Markdown',
        'disable_web_page_preview' => true,
    ],

    // 限流配置
    'rate_limit' => [
        'max_messages_per_second' => 30,
        'max_messages_per_minute' => 20,
    ],
];
```

### 4.5 环境变量（.env）
```env
# TRON配置
TRON_GRID_API_URL=https://api.trongrid.io
TRON_GRID_API_KEY=your-api-key-here
TRON_FULL_NODE=https://api.trongrid.io
TRON_SOLIDITY_NODE=https://api.trongrid.io
TRON_EVENT_SERVER=https://api.trongrid.io
TRON_BLOCK_CONFIRMATION=19

# Telegram Bot配置
TELEGRAM_BOT_TOKEN=your-bot-token-here
TELEGRAM_WEBHOOK_ENABLED=false
TELEGRAM_WEBHOOK_URL=https://yourdomain.com/api/telegram/webhook
```

---

## 五、开发优先级排序

### 🔥 P0（核心基础 - 第1周）
1. ✅ **Repository层（7个类）** - 数据访问基础
   - 工作量：2天
   - 交付：所有Repository类完成并通过单元测试

2. ✅ **TgSnakeGameService** - 游戏核心逻辑
   - 工作量：2天
   - 交付：凭证生成、蛇身拼接、中奖匹配算法

3. ✅ **TgPrizeService** - 中奖处理
   - 工作量：1天
   - 交付：中奖记录创建、奖金计算

4. ✅ **辅助类（TronWebHelper、TelegramBotHelper、TgConstants）**
   - 工作量：1天
   - 交付：工具类和常量定义

---

### ⭐ P1（关键功能 - 第2周）
5. ✅ **TgTronMonitorService** - 区块链监听
   - 工作量：1.5天
   - 交付：交易监听、验证、推送队列

6. ✅ **TronMonitorCrontab** - 交易监听定时任务
   - 工作量：0.5天
   - 交付：定时任务进程

7. ✅ **TronTxProcessQueueConsumer** - 交易处理队列
   - 工作量：2天
   - 交付：队列消费者、完整游戏流程

8. ✅ **TgTransferService** - 自动转账
   - 工作量：1.5天
   - 交付：转账执行、确认、异常处理

9. ✅ **PrizeTransferQueueConsumer** - 转账队列
   - 工作量：1.5天
   - 交付：转账队列消费者

---

### ✅ P2（管理功能 - 第3周）
10. ✅ **TgGameGroupService & TgGameGroupConfigService**
    - 工作量：1天
    - 交付：群组配置管理

11. ✅ **TgGameGroupController** - 群组管理接口
    - 工作量：1天
    - 交付：CRUD接口、统计接口

12. ✅ **TgSnakeGameController** - 游戏监控接口
    - 工作量：0.5天
    - 交付：蛇身查询、节点列表、奖池查询

13. ✅ **TgPrizeRecordController** - 中奖记录接口
    - 工作量：0.5天
    - 交付：中奖记录查询、详情、重试

14. ✅ **TgTelegramBotService** - 机器人服务
    - 工作量：2天
    - 交付：命令处理、消息推送、通知模板

---

### 📊 P3（高级功能 - 第4周）
15. ✅ **TgPlayerWalletService** - 钱包绑定
    - 工作量：1天
    - 交付：绑定、解绑、查询

16. ✅ **TgWalletChangeService** - 钱包变更
    - 工作量：1.5天
    - 交付：开始、取消、完成变更

17. ✅ **WalletChangeNotifierCrontab** - 变更通知
    - 工作量：0.5天
    - 交付：倒计时通知定时任务

18. ✅ **TgStatisticsController** - 数据统计
    - 工作量：1天
    - 交付：概览、群组、租户、手续费统计

19. ✅ **事件系统（3个Event类）**
    - 工作量：1天
    - 交付：中奖事件、钱包变更事件、节点事件

20. ✅ **配置文件调整**
    - 工作量：0.5天
    - 交付：进程配置、事件配置、TRON配置、Telegram配置

---

### 🧪 测试与部署（第4周）
21. ✅ **单元测试**
    - 工作量：1天
    - 覆盖：Repository、Service核心方法

22. ✅ **集成测试**
    - 工作量：1天
    - 覆盖：完整游戏流程、钱包变更流程

23. ✅ **压力测试**
    - 工作量：0.5天
    - 测试：高并发转账、监听性能、队列吞吐

24. ✅ **文档编写**
    - 工作量：1天
    - 交付：API文档、部署文档、运维文档

---

## 六、关键技术点

### 6.1 乐观锁实现（防止并发中奖）
```php
// 在创建中奖记录时使用version字段
public function createPrizeRecord(array $data): PrizeRecord
{
    $groupId = $data['group_id'];

    // 获取当前群组信息
    $group = TgGameGroupRepository::findById($groupId);
    $currentVersion = $group->version;

    // 开启事务
    DB::beginTransaction();
    try {
        // 1. 使用乐观锁更新群组版本号
        $affected = TgGameGroup::where('id', $groupId)
            ->where('version', $currentVersion)
            ->update([
                'version' => $currentVersion + 1,
                'last_prize_amount' => $data['prize_amount'],
                'last_prize_at' => now()
            ]);

        if ($affected === 0) {
            throw new Exception('并发冲突，请重试');
        }

        // 2. 创建中奖记录
        $prizeRecord = TgPrizeRecordRepository::create($data);

        // 3. 标记节点为已中奖
        TgSnakeNodeRepository::whereIn('id', $data['node_ids'])
            ->update([
                'status' => 2,
                'matched_prize_id' => $prizeRecord->id
            ]);

        DB::commit();
        return $prizeRecord;

    } catch (Exception $e) {
        DB::rollback();
        throw $e;
    }
}
```

### 6.2 事务处理（钱包变更原子操作）
```php
public function completeWalletChange(int $groupId): void
{
    DB::transaction(function () use ($groupId) {
        // 1. 获取配置
        $config = TgGameGroupConfigRepository::findById($groupId);

        // 2. 归档所有活跃节点
        $archivedCount = TgSnakeNode::where('group_id', $groupId)
            ->where('status', 1)
            ->update(['status' => 4]);

        // 3. 统计被归档的奖池金额
        $poolAmount = TgSnakeNode::where('group_id', $groupId)
            ->where('status', 4)
            ->where('wallet_cycle', $config->wallet_change_count)
            ->sum('amount');

        // 4. 增加钱包周期计数
        $newWalletCycle = $config->wallet_change_count + 1;

        // 5. 更新配置
        $config->update([
            'wallet_address' => $config->pending_wallet_address,
            'wallet_change_count' => $newWalletCycle,
            'pending_wallet_address' => null,
            'wallet_change_status' => 1,
            'wallet_change_start_at' => null,
            'wallet_change_end_at' => null
        ]);

        // 6. 记录变更日志
        TgGameGroupConfigLogRepository::create([
            'config_id' => $config->id,
            'field' => 'wallet_address',
            'old_value' => $config->getOriginal('wallet_address'),
            'new_value' => $config->wallet_address,
            'change_type' => 'completed',
            'extra_data' => json_encode([
                'archived_count' => $archivedCount,
                'pool_amount' => $poolAmount,
                'old_cycle' => $config->wallet_change_count - 1,
                'new_cycle' => $newWalletCycle
            ])
        ]);

        // 7. 清空群组蛇身
        $group = TgGameGroupRepository::getByConfigId($groupId);
        $group->update([
            'current_snake_nodes' => '',
            'prize_pool_amount' => 0
        ]);

        // 8. 触发事件
        event('tg.wallet.change.completed', [$config, [
            'archived_count' => $archivedCount,
            'pool_amount' => $poolAmount
        ]]);
    });
}
```

### 6.3 队列延迟处理（等待区块确认）
```php
// 在TronMonitorCrontab中，发现新交易后推入队列
public function run(): void
{
    foreach ($transactions as $tx) {
        if ($this->validateTransaction($tx, $config)) {
            // 记录交易日志
            TgTronTransactionLogRepository::create([
                'config_id' => $config->id,
                'tx_hash' => $tx['hash'],
                'from_address' => $tx['from'],
                'to_address' => $tx['to'],
                'amount' => $tx['amount'],
                'block_height' => $tx['block_height'],
                'block_timestamp' => $tx['timestamp'],
                'status' => $tx['status'],
                'is_valid' => 1,
                'processed' => 0
            ]);

            // 推入处理队列（延迟60秒等待19个区块确认）
            Redis::send('tron-tx-process', [
                'config_id' => $config->id,
                'tx_hash' => $tx['hash']
            ], delay: 60);
        }
    }
}
```

### 6.4 Redis队列实现
```php
// 使用Webman的Redis Queue

// 发送消息到队列
use support\Redis;

Redis::send('tron-tx-process', [
    'config_id' => 1,
    'tx_hash' => '0xabc123...'
], delay: 60);  // 延迟60秒

// 队列消费者
namespace app\queue\consumer;

use Webman\RedisQueue\Consumer;

class TronTxProcessQueueConsumer implements Consumer
{
    public $queue = 'tron-tx-process';
    public $connection = 'default';

    public function consume($data)
    {
        $configId = $data['config_id'];
        $txHash = $data['tx_hash'];

        // 处理逻辑
        // ...
    }
}
```

### 6.5 定时任务实现
```php
// 使用Workerman Timer

namespace app\process;

use Workerman\Timer;

class TronMonitorCrontab
{
    public function onWorkerStart($worker)
    {
        // 每3秒执行一次
        Timer::add(3, function() {
            $this->monitorTransactions();
        });
    }

    private function monitorTransactions(): void
    {
        // 监听逻辑
        // ...
    }
}
```

---

## 七、测试计划

### 7.1 单元测试
**测试工具：** PHPUnit

**测试覆盖：**
- **Repository层：** CRUD操作、查询方法、边界条件
  ```php
  // 示例：TgSnakeNodeRepositoryTest
  public function testGetActiveNodes()
  {
      $nodes = $this->repository->getActiveNodes(1);
      $this->assertGreaterThan(0, $nodes->count());
      $this->assertEquals(1, $nodes[0]->status);
  }
  ```

- **Service层：** 业务逻辑、异常处理
  ```php
  // 示例：TgSnakeGameServiceTest
  public function testExtractTicketNumber()
  {
      $ticketNumber = $this->service->extractTicketNumber('abc123def456');
      $this->assertEquals('56', $ticketNumber);
  }

  public function testCheckMatchJackpot()
  {
      $result = $this->service->checkMatch(1, $newNode);
      $this->assertEquals('jackpot', $result['type']);
  }
  ```

- **Helper类：** 工具方法
  ```php
  // 示例：TronWebHelperTest
  public function testValidateAddress()
  {
      $this->assertTrue(TronWebHelper::validateAddress('TXYZa2kR6...'));
      $this->assertFalse(TronWebHelper::validateAddress('invalid'));
  }
  ```

### 7.2 集成测试
**测试场景：**

#### 场景1：完整游戏流程
```php
public function testCompleteGameFlow()
{
    // 1. 模拟TRON转账
    $txHash = $this->mockTronTransaction([
        'from' => 'TPlayer123...',
        'to' => 'TGroup456...',
        'amount' => 5.0
    ]);

    // 2. 触发交易监听
    $this->tronMonitorService->processTransaction($txHash);

    // 3. 等待队列处理
    sleep(2);

    // 4. 验证购彩凭证已创建
    $node = TgSnakeNodeRepository::findByTxHash($txHash);
    $this->assertNotNull($node);
    $this->assertEquals('23', $node->ticket_number);

    // 5. 模拟中奖匹配
    $matchResult = $this->snakeGameService->checkMatch(1, $node);

    // 6. 验证中奖记录已创建
    if ($matchResult) {
        $prizeRecord = TgPrizeRecordRepository::latest()->first();
        $this->assertEquals(1, $prizeRecord->status); // pending

        // 7. 等待转账队列处理
        sleep(5);

        // 8. 验证转账完成
        $prizeRecord->refresh();
        $this->assertEquals(3, $prizeRecord->status); // completed
    }
}
```

#### 场景2：钱包变更流程
```php
public function testWalletChangeFlow()
{
    // 1. 创建测试群组和节点
    $group = $this->createTestGroup();
    $nodes = $this->createTestNodes($group->id, 5);

    // 2. 开始钱包变更
    $this->walletChangeService->startWalletChange(
        $group->id,
        'TNewAddress...',
        'admin'
    );

    // 3. 验证状态变更
    $config = TgGameGroupConfigRepository::findById($group->id);
    $this->assertEquals(2, $config->wallet_change_status);

    // 4. 模拟10分钟后自动完成
    $this->travelTo(now()->addMinutes(10));
    $this->walletChangeService->completeWalletChange($group->id);

    // 5. 验证节点归档
    $archivedNodes = TgSnakeNodeRepository::where('group_id', $group->id)
        ->where('status', 4)
        ->get();
    $this->assertEquals(5, $archivedNodes->count());

    // 6. 验证配置更新
    $config->refresh();
    $this->assertEquals('TNewAddress...', $config->wallet_address);
    $this->assertEquals(1, $config->wallet_change_count);
}
```

#### 场景3：异常场景处理
```php
public function testInsufficientBalance()
{
    // 1. 设置热钱包余额不足
    $this->mockTronBalance('THotWallet...', 1.0);

    // 2. 创建中奖记录（需要转账10 TRX）
    $prizeRecord = $this->createTestPrizeRecord(['prize_per_winner' => 10]);

    // 3. 触发转账
    Redis::send('tg-prize-transfer', ['prize_record_id' => $prizeRecord->id]);

    // 4. 等待队列处理
    sleep(2);

    // 5. 验证转账失败
    $transfers = TgPrizeTransferRepository::getByPrizeRecordId($prizeRecord->id);
    $this->assertEquals(4, $transfers[0]->status); // failed

    // 6. 验证告警通知已发送
    $this->assertNotificationSent('余额不足');
}
```

### 7.3 压力测试
**测试工具：** Apache Bench (ab), wrk

#### 测试1：高并发转账
```bash
# 模拟1000个玩家同时转账
ab -n 1000 -c 100 -p transaction.json \
   http://api.trongrid.io/wallet/createtransaction

# 预期结果：
# - 所有交易成功记录到数据库
# - 无重复购彩凭证
# - 无并发中奖错误
```

#### 测试2：监听性能
```php
public function testMonitorPerformance()
{
    // 1. 创建100个群组
    $groups = $this->createTestGroups(100);

    // 2. 模拟每个群组有10笔新交易
    foreach ($groups as $group) {
        $this->mockTransactions($group->wallet_address, 10);
    }

    // 3. 记录开始时间
    $startTime = microtime(true);

    // 4. 执行一次监听
    $this->tronMonitorCrontab->run();

    // 5. 记录结束时间
    $endTime = microtime(true);
    $duration = $endTime - $startTime;

    // 6. 验证性能指标
    $this->assertLessThan(3, $duration); // 应在3秒内完成

    // 7. 验证所有交易已推入队列
    $queueSize = Redis::llen('tron-tx-process');
    $this->assertEquals(1000, $queueSize);
}
```

#### 测试3：队列吞吐量
```php
public function testQueueThroughput()
{
    // 1. 推入1000条消息到转账队列
    for ($i = 0; $i < 1000; $i++) {
        Redis::send('tg-prize-transfer', ['prize_record_id' => $i]);
    }

    // 2. 记录开始时间
    $startTime = microtime(true);

    // 3. 启动消费者进程（3个进程）
    // 手动启动或通过命令行

    // 4. 等待队列清空
    while (Redis::llen('tg-prize-transfer') > 0) {
        sleep(1);
    }

    // 5. 记录结束时间
    $endTime = microtime(true);
    $duration = $endTime - $startTime;

    // 6. 计算吞吐量
    $throughput = 1000 / $duration;
    $this->assertGreaterThan(10, $throughput); // 至少10笔/秒
}
```

---

## 八、预估工作量

| 阶段 | 具体任务 | 工作量（天） | 说明 |
|------|---------|------------|------|
| **阶段一：Repository层** | 7个Repository类 | 2 | 数据访问层，结构清晰 |
| **阶段二：Service层** | TgSnakeGameService | 2 | 核心游戏逻辑，最复杂 |
|  | TgPrizeService | 1 | 中奖处理 |
|  | TgTronMonitorService | 1.5 | 区块链监听 |
|  | TgTransferService | 1.5 | 自动转账 |
|  | TgTelegramBotService | 2 | 机器人服务 |
|  | TgPlayerWalletService | 1 | 钱包绑定 |
|  | TgWalletChangeService | 1.5 | 钱包变更 |
|  | TgGameGroupService等 | 1 | 其他Service |
| **阶段三：Controller层** | 4个Controller类 | 2 | HTTP接口封装 |
| **阶段四：Process/Queue** | 4个Process/Consumer类 | 3 | 定时任务和队列消费 |
| **阶段五：辅助类** | 3个Helper/Constants类 | 1 | 工具类和常量 |
| **阶段六：事件系统** | 3个Event类 | 1 | 事件处理 |
| **配置文件调整** | 4个配置文件 | 0.5 | 配置文件修改 |
| **测试与调试** | 单元测试、集成测试、压力测试 | 3 | 保证质量 |
| **文档编写** | API文档、部署文档、运维文档 | 1 | 交付文档 |
| **总计** |  | **23.5天** | 约5周（含缓冲时间） |

**注意事项：**
- 以上工作量为单人全职开发的预估时间
- 如有团队协作，可并行开发Repository、Service等模块
- 实际开发中可能遇到技术难点需要额外时间调试
- 建议预留10-15%的缓冲时间处理突发问题

---

## 九、风险提示与应对

### 🔴 高风险项

#### 1. TRON网络稳定性
**风险描述：**
- TronGrid API可能限流（免费版每秒5次请求）
- 节点同步延迟导致交易查询不完整
- 网络拥堵时Gas费用暴涨

**应对措施：**
- 使用多个API Key轮询
- 自建TRON节点（FullNode + SolidityNode）
- 设置API请求重试机制（最多3次）
- 监控API响应时间，超过3秒切换备用节点
- 预留充足的TRX用于Gas费用

#### 2. 并发中奖处理
**风险描述：**
- 多个玩家同时转账可能触发并发中奖
- 奖池金额计算错误
- 中奖记录重复创建

**应对措施：**
- 使用乐观锁（version字段）防止并发修改
- 使用数据库事务保证原子性
- 中奖检测前先加分布式锁
- 记录详细日志便于问题排查

#### 3. 钱包安全
**风险描述：**
- 热钱包私钥泄露
- 热钱包余额被盗
- 内部人员滥用权限

**应对措施：**
- 私钥使用AES-256加密存储
- 热钱包只存放必要的运营资金（建议不超过100,000 TRX）
- 大额资金存放在冷钱包，定期人工充值热钱包
- 设置转账告警：单笔超过1000 TRX立即通知管理员
- 实施IP白名单，限制热钱包转账权限
- 定期审计钱包流水

---

### 🟡 中风险项

#### 4. Telegram Bot限流
**风险描述：**
- Telegram Bot API限流（每秒30条消息）
- 高峰期消息发送失败
- 大群组消息延迟

**应对措施：**
- 实现消息队列，控制发送频率
- 批量消息合并发送（如多个购彩通知合并）
- 失败消息自动重试（最多3次）
- 使用异步发送，不阻塞主流程

#### 5. 队列积压
**风险描述：**
- 高峰期交易处理队列积压
- 转账队列消费速度慢
- 玩家等待时间过长

**应对措施：**
- 增加队列消费者进程数（可动态调整）
- 监控队列长度，超过1000条告警
- 优化队列消费逻辑，减少不必要的数据库查询
- 使用Redis缓存热点数据

#### 6. 数据一致性
**风险描述：**
- 分布式事务处理失败
- 交易记录与蛇身节点不一致
- 中奖记录与转账记录不匹配

**应对措施：**
- 关键操作使用数据库事务
- 实现幂等性：同一笔交易哈希只处理一次
- 记录详细日志，便于数据对账
- 定期运行数据一致性检查脚本

---

### 🟢 低风险项

#### 7. 用户体验
**风险描述：**
- 通知消息格式不友好
- 命令使用不便
- 游戏规则不清晰

**应对措施：**
- 优化消息模板，使用Emoji增强可读性
- 提供详细的帮助文档（/help, /rules）
- 收集用户反馈，持续迭代优化

#### 8. 性能优化
**风险描述：**
- 数据库查询慢
- 内存占用高
- 响应时间长

**应对措施：**
- 关键查询添加索引
- 使用Redis缓存热点数据
- 定期清理历史数据（如3个月前的归档节点）
- 监控服务器性能指标

#### 9. 监控告警
**风险描述：**
- 异常情况不能及时发现
- 服务宕机无人知晓
- 数据异常难以追溯

**应对措施：**
- 接入监控系统（如Prometheus + Grafana）
- 配置告警规则（队列积压、余额不足、API错误率等）
- 记录详细日志（Info、Warning、Error级别）
- 定期查看日志，排查潜在问题

---

## 十、下一步行动

### 📅 第一周（P0优先级）

#### Day 1-2：Repository层
- [ ] 创建7个Repository类
- [ ] 实现handleSearch方法
- [ ] 实现自定义查询方法
- [ ] 编写单元测试

#### Day 3-4：核心游戏逻辑
- [ ] 实现TgSnakeGameService
  - [ ] extractTicketNumber() - 凭证提取
  - [ ] generateTicketSerialNo() - 流水号生成
  - [ ] checkMatch() - 中奖匹配算法
- [ ] 实现TgPrizeService
  - [ ] calculatePrize() - 奖金计算
  - [ ] createPrizeRecord() - 中奖记录创建
- [ ] 编写单元测试

#### Day 5：辅助类
- [ ] 实现TronWebHelper
- [ ] 实现TelegramBotHelper
- [ ] 定义TgConstants
- [ ] 编写单元测试

---

### 📅 第二周（P1优先级）

#### Day 6-7：区块链监听
- [ ] 实现TgTronMonitorService
- [ ] 实现TronMonitorCrontab定时任务
- [ ] 配置进程启动
- [ ] 测试交易监听功能

#### Day 8-9：交易处理
- [ ] 实现TronTxProcessQueueConsumer
- [ ] 集成完整游戏流程
  - 交易监听 → 凭证生成 → 蛇身拼接 → 中奖匹配
- [ ] 测试端到端流程

#### Day 10-12：自动转账
- [ ] 实现TgTransferService
- [ ] 实现PrizeTransferQueueConsumer
- [ ] 测试转账功能
- [ ] 测试异常处理

---

### 📅 第三周（P2优先级）

#### Day 13-14：后台管理接口
- [ ] 实现TgGameGroupService
- [ ] 实现TgGameGroupController
- [ ] 实现TgSnakeGameController
- [ ] 实现TgPrizeRecordController
- [ ] 测试所有接口

#### Day 15-17：Telegram Bot
- [ ] 实现TgTelegramBotService
- [ ] 实现命令处理逻辑
- [ ] 实现消息推送
- [ ] 设计消息模板
- [ ] 测试Bot交互

---

### 📅 第四周（P3优先级 + 测试）

#### Day 18-19：高级功能
- [ ] 实现TgPlayerWalletService
- [ ] 实现TgWalletChangeService
- [ ] 实现WalletChangeNotifierCrontab
- [ ] 实现TgStatisticsController
- [ ] 实现事件系统（3个Event类）

#### Day 20：配置与部署
- [ ] 调整配置文件
- [ ] 编写部署脚本
- [ ] 配置环境变量

#### Day 21-22：测试
- [ ] 单元测试补充
- [ ] 集成测试
- [ ] 压力测试
- [ ] 修复Bug

#### Day 23：文档与上线
- [ ] 编写API文档
- [ ] 编写部署文档
- [ ] 编写运维文档
- [ ] 准备上线

---

## 十一、总结

### ✅ 改造优势

1. **保留原有架构** - 无需大规模重构，风险可控
2. **复用基础设施** - 租户系统、权限系统、事件系统直接使用
3. **独立模块设计** - TG游戏功能与支付业务完全隔离
4. **代码风格一致** - 遵循现有命名规范和设计模式
5. **易于维护扩展** - 清晰的分层架构，便于后续迭代

---

### 📊 关键指标

- **代码复用率：** 约60%（租户、权限、事件、队列、ORM等）
- **新增代码量：** 约32个类文件（不含测试）
  - Repository: 7个
  - Service: 8个
  - Controller: 4个
  - Process/Queue: 4个
  - Helper/Constants: 3个
  - Event: 3个
  - Config: 3个
- **数据库表：** 10个（已完成模型创建）
- **HTTP接口：** 约25个RESTful API
- **定时任务：** 2个（TRON监听、钱包变更通知）
- **队列消费者：** 2个（交易处理、奖金转账）

---

### 🎯 交付成果

1. **完整的Telegram贪吃蛇游戏系统**
   - 自动化交易监听
   - 实时中奖匹配
   - 自动奖金分配

2. **后台管理系统**
   - 群组配置管理
   - 游戏状态监控
   - 中奖记录查询
   - 数据统计分析

3. **Telegram Bot集成**
   - 20+命令支持
   - 实时消息推送
   - 钱包绑定功能

4. **完善的监控与日志**
   - 操作日志
   - 错误日志
   - 性能监控
   - 告警通知

5. **完整的技术文档**
   - API接口文档
   - 部署运维文档
   - 开发者文档
   - 用户操作手册

---

### 🚀 后续规划

#### 短期优化（1-2个月）
- 性能优化：缓存策略、SQL优化
- 用户体验：消息模板优化、命令简化
- 监控告警：接入专业监控系统
- 数据分析：游戏数据可视化

#### 中期扩展（3-6个月）
- 多链支持：以太坊、BSC、Polygon
- 游戏模式：快速模式、PK模式、赛季模式
- 社交功能：排行榜、成就系统、邀请奖励
- NFT集成：购彩凭证NFT化

#### 长期规划（6-12个月）
- 去中心化改造：智能合约托管奖池
- 跨链支持：支持更多公链
- DAO治理：社区参与决策
- 商业化：租户订阅、广告变现

---

**准备就绪，等待开始执行！** 🚀

如有任何疑问或需要调整的地方，请随时反馈。我将严格按照此计划逐步实施改造工作。
