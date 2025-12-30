<?php

namespace app\service\bot;

use app\lib\helper\TelegramBotHelper;
use app\lib\helper\TronWebHelper;
use app\service\TgGameGroupConfigService;
use app\service\TgGameGroupService;
use app\service\TgPlayerWalletBindingService;
use app\service\TgPrizeService;
use app\service\TgSnakeNodeService;
use app\service\TgTronMonitorService;
use DI\Attribute\Inject;
use support\Log;

/**
 * Telegram贪吃蛇游戏Bot命令服务
 */
class TgBotCommandService
{
    #[Inject]
    protected TgGameGroupConfigService $configService;

    #[Inject]
    protected TgGameGroupService $groupService;

    #[Inject]
    protected TgPlayerWalletBindingService $bindingService;

    #[Inject]
    protected TgSnakeNodeService $nodeService;

    #[Inject]
    protected TgPrizeService $prizeService;

    #[Inject]
    protected TgTronMonitorService $tronMonitorService;

    #[Inject]
    protected TronWebHelper $tronHelper;

    /**
     * 验证是否为群组管理员
     * @param int $chatId 群组ID
     * @param int $userId 用户ID
     * @return bool
     */
    protected function isGroupAdmin(int $chatId, int $userId): bool
    {
        try {
            // 调用Telegram API验证用户是否为群组管理员
            return TelegramBotHelper::checkAdmin($chatId, $userId);
        } catch (\Throwable $e) {
            Log::error("验证群组管理员失败: " . $e->getMessage(), [
                'chat_id' => $chatId,
                'user_id' => $userId,
                'trace' => $e->getTraceAsString()
            ]);
            // 如果API调用失败，出于安全考虑返回false
            return false;
        }
    }

    /**
     * 处理命令
     * @param string $command 命令名
     * @param array $params 命令参数
     * @param array $messageData Telegram消息数据
     * @return array 返回响应
     */
    public function handleCommand(string $command, array $params, array $messageData): array
    {
        $chatId = $messageData['chat_id'];
        $userId = $messageData['from_user_id'];
        $username = $messageData['from_username'] ?? null;

        try {
            return match ($command) {
                'Help', 'cnHelp' => $this->handleHelp($chatId, $command === 'cnHelp'),
                'Start', 'cnStart' => $this->handleStart($chatId, $command === 'cnStart'),
                'Rules', 'cnRules' => $this->handleRules($chatId, $command === 'cnRules'),
                'Snake', 'cnSnake' => $this->handleSnake($chatId, $command === 'cnSnake'),
                'BindWallet', 'cnBindWallet' => $this->handleBindWallet($chatId, $userId, $username, $params, $command === 'cnBindWallet'),
                'UnbindWallet', 'cnUnbindWallet' => $this->handleUnbindWallet($chatId, $userId, $command === 'cnUnbindWallet'),
                'MyWallet', 'cnMyWallet' => $this->handleMyWallet($chatId, $userId, $command === 'cnMyWallet'),
                'MyTickets', 'cnMyTickets' => $this->handleMyTickets($chatId, $userId, $command === 'cnMyTickets'),
                'MyWins', 'cnMyWins' => $this->handleMyWins($chatId, $userId, $command === 'cnMyWins'),
                'PrizePool', 'cnPrizePool' => $this->handlePrizePool($chatId, $command === 'cnPrizePool'),
                'RecentWins', 'cnRecentWins' => $this->handleRecentWins($chatId, $command === 'cnRecentWins'),
                'Stats', 'cnStats' => $this->handleStats($chatId, $command === 'cnStats'),
                'WalletChange', 'cnWalletChange' => $this->handleWalletChange($chatId, $userId, $params, $command === 'cnWalletChange'),
                'CancelWalletChange', 'cnCancelWalletChange' => $this->handleCancelWalletChange($chatId, $userId, $command === 'cnCancelWalletChange'),
                'GroupConfig', 'cnGroupConfig' => $this->handleGroupConfig($chatId, $userId, $command === 'cnGroupConfig'),
                'GetId', 'cnGetId' => $this->handleGetId($userId, $command === 'cnGetId'),
                'GetGroupId', 'cnGetGroupId' => $this->handleGetGroupId($chatId, $command === 'cnGetGroupId'),
                // 管理员初始化指令
                'BindTenant', 'cnBindTenant' => $this->handleBindTenant($chatId, $userId, $params, $messageData, $command === 'cnBindTenant'),
                'SetWallet', 'cnSetWallet' => $this->handleSetWallet($chatId, $userId, $params, $command === 'cnSetWallet'),
                'SetBetAmount', 'cnSetBetAmount' => $this->handleSetBetAmount($chatId, $userId, $params, $command === 'cnSetBetAmount'),
                // 管理员白名单管理
                'AddAdmin', 'cnAddAdmin' => $this->handleAddAdmin($chatId, $userId, $params, $messageData, $command === 'cnAddAdmin'),
                'RemoveAdmin', 'cnRemoveAdmin' => $this->handleRemoveAdmin($chatId, $userId, $params, $messageData, $command === 'cnRemoveAdmin'),
                'ListAdmins', 'cnListAdmins' => $this->handleListAdmins($chatId, $userId, $command === 'cnListAdmins'),
                default => $this->handleUnknown(str_starts_with($command, 'cn')),
            };
        } catch (\Throwable $e) {
            Log::error("处理命令失败: {$command}", [
                'error' => $e->getMessage(),
                'chat_id' => $chatId,
                'user_id' => $userId,
            ]);
            return [
                'success' => false,
                'message' => str_starts_with($command, 'cn') ? '命令处理失败，请稍后重试' : 'Command processing failed, please try again later',
            ];
        }
    }

    /**
     * 帮助命令
     */
    protected function handleHelp(int $chatId, bool $isCn): array
    {
        $helpText = implode("\n", CommandEnum::getHelpReply($isCn));
        return ['success' => true, 'message' => $helpText];
    }

    /**
     * 开始命令
     */
    protected function handleStart(int $chatId, bool $isCn): array
    {
        $text = $isCn
            ? "🐍 欢迎来到贪吃蛇链上游戏！\n\n" .
              "游戏规则：\n" .
              "1. 向群组钱包地址转账TRX参与游戏\n" .
              "2. 每笔转账生成一个票号，票号提取自交易哈希\n" .
              "3. 票号按时间顺序组成蛇身\n" .
              "4. 当蛇头（最新票号）与蛇身任意节点匹配时触发中奖\n\n" .
              "使用 /help 查看所有命令\n" .
              "使用 /rules 查看详细规则\n" .
              "使用 /bind_wallet 绑定您的钱包地址"
            : "🐍 Welcome to Snake Chain Game!\n\n" .
              "Game Rules:\n" .
              "1. Transfer TRX to the group wallet to participate\n" .
              "2. Each transaction generates a ticket number from the TX hash\n" .
              "3. Ticket numbers form the snake body in chronological order\n" .
              "4. Prize is triggered when the snake head matches any body node\n\n" .
              "Use /help to see all commands\n" .
              "Use /rules for detailed rules\n" .
              "Use /bind_wallet to bind your wallet address";

        return ['success' => true, 'message' => $text];
    }

    /**
     * 规则命令
     */
    protected function handleRules(int $chatId, bool $isCn): array
    {
        $config = $this->configService->getByTgChatId($chatId);
        if (!$config) {
            return [
                'success' => false,
                'message' => $isCn ? '群组未配置' : 'Group not configured',
            ];
        }

        // 计算平台手续费率（转换为百分比）
        $platformFeePercent = round($config->platform_fee_rate * 100, 2);
        // 计算玩家实际可得奖金比例
        $playerPrizePercent = round((1 - $config->platform_fee_rate) * 100, 2);

        $text = $isCn
            ? "🐍 贪吃蛇链上游戏规则\n\n" .
              "━━━━━━━━━━━━━━━━━━━━\n" .
              "【💰 投注要求】\n" .
              "• 固定投注金额：<b>{$config->bet_amount} TRX</b>\n" .
              "• 收款钱包：<code>{$config->wallet_address}</code>\n" .
              "• 必须使用已绑定的钱包地址转账\n" .
              "• 转账金额必须完全匹配（不多不少）\n\n" .
              "━━━━━━━━━━━━━━━━━━━━\n" .
              "【🎮 游戏机制】\n" .
              "1️⃣ 每笔有效转账生成一个「票号」\n" .
              "2️⃣ 票号从交易哈希中提取（取哈希末尾数字）\n" .
              "3️⃣ 所有票号按时间顺序组成「蛇身」\n" .
              "4️⃣ 最新的票号称为「蛇头」\n" .
              "5️⃣ 当蛇头与蛇身中任意节点匹配时触发中奖\n\n" .
              "━━━━━━━━━━━━━━━━━━━━\n" .
              "【🏆 中奖规则】\n" .
              "• <b>完全匹配（Jackpot）</b>：蛇头与蛇身某节点完全相同\n" .
              "  → 获得当前奖池所有金额\n" .
              "• <b>部分匹配（范围奖）</b>：蛇头与蛇身某节点部分相同\n" .
              "  → 获得固定金额奖励\n\n" .
              "━━━━━━━━━━━━━━━━━━━━\n" .
              "【💵 奖金分配】\n" .
              "• 平台手续费：<b>{$platformFeePercent}%</b>\n" .
              "• 玩家奖金池：<b>{$playerPrizePercent}%</b>\n" .
              "• 手续费从每笔投注中扣除\n" .
              "• 剩余金额进入奖池累积\n" .
              "• 中奖时自动转账到绑定钱包\n\n" .
              "━━━━━━━━━━━━━━━━━━━━\n" .
              "【📝 参与步骤】\n" .
              "1️⃣ 绑定钱包：<code>/bind_wallet 您的TRON地址</code>\n" .
              "2️⃣ 查看收款地址：<code>/address</code>\n" .
              "3️⃣ 转账 {$config->bet_amount} TRX 到群组钱包\n" .
              "4️⃣ 等待区块确认（约1分钟）\n" .
              "5️⃣ 系统自动生成票号并检测中奖\n" .
              "6️⃣ 中奖后自动转账到您的钱包\n\n" .
              "━━━━━━━━━━━━━━━━━━━━\n" .
              "【⚠️ 重要提示】\n" .
              "• 必须先绑定钱包才能参与游戏\n" .
              "• 转账金额必须精确为 {$config->bet_amount} TRX\n" .
              "• 只能从绑定的钱包地址转账\n" .
              "• 转账到其他地址无效\n" .
              "• 请勿重复转账，每笔都会计入\n\n" .
              "💡 使用 <code>/help</code> 查看所有命令\n" .
              "💡 使用 <code>/snake</code> 查看当前蛇身状态"
            : "🐍 Snake Chain Game Rules\n\n" .
              "━━━━━━━━━━━━━━━━━━━━\n" .
              "【💰 Bet Requirements】\n" .
              "• Fixed Bet Amount: <b>{$config->bet_amount} TRX</b>\n" .
              "• Wallet Address: <code>{$config->wallet_address}</code>\n" .
              "• Must use a bound wallet address\n" .
              "• Transfer amount must be exact (not more or less)\n\n" .
              "━━━━━━━━━━━━━━━━━━━━\n" .
              "【🎮 Game Mechanics】\n" .
              "1️⃣ Each valid transfer generates a 'ticket number'\n" .
              "2️⃣ Ticket number extracted from transaction hash (last digits)\n" .
              "3️⃣ All tickets form the 'snake body' in chronological order\n" .
              "4️⃣ The latest ticket is called the 'snake head'\n" .
              "5️⃣ Prize triggered when head matches any body node\n\n" .
              "━━━━━━━━━━━━━━━━━━━━\n" .
              "【🏆 Winning Rules】\n" .
              "• <b>Perfect Match (Jackpot)</b>: Head completely matches a body node\n" .
              "  → Win entire current prize pool\n" .
              "• <b>Partial Match (Range Prize)</b>: Head partially matches a body node\n" .
              "  → Win fixed prize amount\n\n" .
              "━━━━━━━━━━━━━━━━━━━━\n" .
              "【💵 Prize Distribution】\n" .
              "• Platform Fee: <b>{$platformFeePercent}%</b>\n" .
              "• Player Prize Pool: <b>{$playerPrizePercent}%</b>\n" .
              "• Fee deducted from each bet\n" .
              "• Remaining amount added to prize pool\n" .
              "• Winners receive automatic transfer to bound wallet\n\n" .
              "━━━━━━━━━━━━━━━━━━━━\n" .
              "【📝 How to Participate】\n" .
              "1️⃣ Bind wallet: <code>/bind_wallet YOUR_TRON_ADDRESS</code>\n" .
              "2️⃣ Check wallet address: <code>/address</code>\n" .
              "3️⃣ Transfer {$config->bet_amount} TRX to group wallet\n" .
              "4️⃣ Wait for block confirmation (~1 minute)\n" .
              "5️⃣ System auto-generates ticket and checks for wins\n" .
              "6️⃣ Auto-transfer to your wallet if you win\n\n" .
              "━━━━━━━━━━━━━━━━━━━━\n" .
              "【⚠️ Important Notes】\n" .
              "• Must bind wallet before participating\n" .
              "• Transfer amount must be exactly {$config->bet_amount} TRX\n" .
              "• Only transfers from bound wallet are valid\n" .
              "• Transfers to other addresses are invalid\n" .
              "• Avoid duplicate transfers, each counts\n\n" .
              "💡 Use <code>/help</code> to see all commands\n" .
              "💡 Use <code>/snake</code> to view current snake status";

        return ['success' => true, 'message' => $text];
    }

    /**
     * 蛇身命令
     * @param int $chatId 群组ID
     * @param bool $isCn 是否中文
     * @param int $page 页码（从1开始）
     * @return array
     */
    protected function handleSnake(int $chatId, bool $isCn, int $page = 1): array
    {
        $config = $this->configService->getByTgChatId($chatId);
        if (!$config) {
            return [
                'success' => false,
                'message' => $isCn ? '群组未配置' : 'Group not configured',
            ];
        }

        $group = $this->groupService->getByConfigId($config->id);
        if (!$group) {
            return [
                'success' => false,
                'message' => $isCn ? '群组未找到' : 'Group not found',
            ];
        }

        $perPage = 10; // 每页显示10条
        $page = max(1, $page); // 确保页码至少为1

        // 获取活跃节点（按创建时间倒序，最新的在前面）
        $activeNodes = $this->nodeService->getActiveNodes($group->id);
        $snakeCount = $activeNodes->count();
        $totalPages = max(1, ceil($snakeCount / $perPage));
        $page = min($page, $totalPages); // 确保页码不超过总页数

        // 获取蛇头票号（最新的节点）
        $snakeHeadTicket = $isCn ? '暂无' : 'None';
        $snakeHeadSerialNo = '';
        if ($snakeCount > 0) {
            /** @var \app\model\ModelTgSnakeNode $firstNode */
            $firstNode = $activeNodes->first();
            $snakeHeadTicket = $firstNode->ticket_number;
            $snakeHeadSerialNo = $firstNode->ticket_serial_no;
        }

        $text = $isCn
            ? "🐍 当前蛇身状态\n\n" .
              "蛇身长度：{$snakeCount} 节\n" .
              "蛇头票号：{$snakeHeadTicket}" . ($snakeHeadSerialNo ? " ({$snakeHeadSerialNo})" : "") . "\n\n" .
              "节点列表（第 {$page}/{$totalPages} 页）：\n"
            : "🐍 Current Snake Status\n\n" .
              "Snake Length: {$snakeCount} nodes\n" .
              "Snake Head: {$snakeHeadTicket}" . ($snakeHeadSerialNo ? " ({$snakeHeadSerialNo})" : "") . "\n\n" .
              "Node List (Page {$page}/{$totalPages}):\n";

        // 分页获取节点
        $offset = ($page - 1) * $perPage;
        $pageNodes = $activeNodes->slice($offset, $perPage);

        foreach ($pageNodes as $node) {
            // 显示流水号、票号和钱包地址后8位
            $walletSuffix = substr($node->player_address, -8);
            $text .= "{$node->ticket_serial_no} | 🎫{$node->ticket_number} | 💳...{$walletSuffix}\n";
        }

        if ($snakeCount == 0) {
            $text .= $isCn ? "暂无节点\n" : "No nodes yet\n";
        }

        // 构建分页按钮
        $inlineKeyboard = null;
        if ($totalPages > 1) {
            $buttons = [];

            // 上一页按钮
            if ($page > 1) {
                $buttons[] = [
                    'text' => $isCn ? '⬅️ 上一页' : '⬅️ Prev',
                    'callback_data' => "snake_page:" . ($page - 1) . ":" . ($isCn ? '1' : '0'),
                ];
            }

            // 页码显示
            $buttons[] = [
                'text' => "{$page}/{$totalPages}",
                'callback_data' => "snake_page:{$page}:" . ($isCn ? '1' : '0'),
            ];

            // 下一页按钮
            if ($page < $totalPages) {
                $buttons[] = [
                    'text' => $isCn ? '下一页 ➡️' : 'Next ➡️',
                    'callback_data' => "snake_page:" . ($page + 1) . ":" . ($isCn ? '1' : '0'),
                ];
            }

            $inlineKeyboard = [$buttons];
        }

        return [
            'success' => true,
            'message' => $text,
            'inline_keyboard' => $inlineKeyboard,
        ];
    }

    /**
     * 蛇身分页回调处理（供TelegramService调用）
     * @param int $chatId 群组ID
     * @param bool $isCn 是否中文
     * @param int $page 页码
     * @return array
     */
    public function handleSnakeCallback(int $chatId, bool $isCn, int $page): array
    {
        return $this->handleSnake($chatId, $isCn, $page);
    }

    /**
     * 绑定钱包命令
     */
    protected function handleBindWallet(int $chatId, int $userId, ?string $username, array $params, bool $isCn): array
    {
        if (empty($params[0])) {
            return [
                'success' => false,
                'message' => $isCn ? '请提供钱包地址' : 'Please provide wallet address',
            ];
        }

        $walletAddress = $params[0];

        // 验证TRON钱包地址格式
        if (!TronWebHelper::isValidAddress($walletAddress)) {
            return [
                'success' => false,
                'message' => $isCn
                    ? '❌ 无效的TRON钱包地址格式'
                    : '❌ Invalid TRON wallet address format',
            ];
        }

        $config = $this->configService->getByTgChatId($chatId);
        if (!$config) {
            return [
                'success' => false,
                'message' => $isCn ? '群组未配置' : 'Group not configured',
            ];
        }

        $result = $this->bindingService->bindWallet([
            'group_id' => $config->id,
            'tg_user_id' => $userId,
            'tg_username' => $username,
            'wallet_address' => $walletAddress,
        ]);

        if ($result['success']) {
            return [
                'success' => true,
                'message' => $isCn
                    ? "✅ 钱包绑定成功！\n地址：{$walletAddress}"
                    : "✅ Wallet bound successfully!\nAddress: {$walletAddress}",
            ];
        }

        return [
            'success' => false,
            'message' => $result['message'],
        ];
    }

    /**
     * 解绑钱包命令
     */
    protected function handleUnbindWallet(int $chatId, int $userId, bool $isCn): array
    {
        $config = $this->configService->getByTgChatId($chatId);
        if (!$config) {
            return [
                'success' => false,
                'message' => $isCn ? '群组未配置' : 'Group not configured',
            ];
        }

        $result = $this->bindingService->unbindWallet($config->id, $userId);

        if ($result['success']) {
            return [
                'success' => true,
                'message' => $isCn ? '✅ 钱包已解绑' : '✅ Wallet unbound successfully',
            ];
        }

        return [
            'success' => false,
            'message' => $result['message'],
        ];
    }

    /**
     * 我的钱包命令
     */
    protected function handleMyWallet(int $chatId, int $userId, bool $isCn): array
    {
        $config = $this->configService->getByTgChatId($chatId);
        if (!$config) {
            return [
                'success' => false,
                'message' => $isCn ? '群组未配置' : 'Group not configured',
            ];
        }

        $binding = $this->bindingService->getUserByTgUserId($config->id, $userId);
        if (!$binding) {
            return [
                'success' => false,
                'message' => $isCn ? '您还没有绑定钱包' : 'You have not bound a wallet yet',
            ];
        }

        $text = $isCn
            ? "💼 我的钱包信息\n\n" .
              "钱包地址：{$binding->wallet_address}\n" .
              "绑定时间：{$binding->created_at}"
            : "💼 My Wallet Info\n\n" .
              "Wallet Address: {$binding->wallet_address}\n" .
              "Bound At: {$binding->created_at}";

        return ['success' => true, 'message' => $text];
    }

    /**
     * 我的票号命令
     */
    protected function handleMyTickets(int $chatId, int $userId, bool $isCn): array
    {
        $config = $this->configService->getByTgChatId($chatId);
        if (!$config) {
            return [
                'success' => false,
                'message' => $isCn ? '群组未配置' : 'Group not configured',
            ];
        }

        $group = $this->groupService->getByConfigId($config->id);
        if (!$group) {
            return [
                'success' => false,
                'message' => $isCn ? '群组未找到' : 'Group not found',
            ];
        }

        // 获取用户在当前蛇身中的节点
        $nodes = $this->nodeService->getPlayerActiveNodes($group->id, $userId);

        if ($nodes->isEmpty()) {
            return [
                'success' => true,
                'message' => $isCn ? '您还没有参与游戏' : 'You have not participated yet',
            ];
        }

        $ticketList = $nodes->map(function ($node) use ($isCn) {
            return $isCn
                ? "票号：{$node->ticket_number} | 投注：{$node->amount} TRX | 流水号：{$node->ticket_serial_no}"
                : "Ticket: {$node->ticket_number} | Bet: {$node->amount} TRX | Serial: {$node->ticket_serial_no}";
        })->join("\n");

        $text = $isCn
            ? "🎫 我的票号\n\n" .
              "总数：{$nodes->count()}\n\n" .
              $ticketList
            : "🎫 My Tickets\n\n" .
              "Total: {$nodes->count()}\n\n" .
              $ticketList;

        return ['success' => true, 'message' => $text];
    }

    /**
     * 我的中奖命令
     */
    protected function handleMyWins(int $chatId, int $userId, bool $isCn): array
    {
        $config = $this->configService->getByTgChatId($chatId);
        if (!$config) {
            return [
                'success' => false,
                'message' => $isCn ? '群组未配置' : 'Group not configured',
            ];
        }

        $group = $this->groupService->getByConfigId($config->id);
        if (!$group) {
            return [
                'success' => false,
                'message' => $isCn ? '群组未找到' : 'Group not found',
            ];
        }

        // 获取用户的中奖记录（最近10条）
        $winRecords = $this->prizeService->getPlayerWinRecords($group->id, $userId, 10);

        if ($winRecords->isEmpty()) {
            return [
                'success' => true,
                'message' => $isCn ? '您还没有中奖记录' : 'No winning records yet',
            ];
        }

        $winList = $winRecords->map(function ($record) use ($isCn) {
            return $isCn
                ? "🏆 票号：{$record->ticket_number} | 奖金：{$record->prize_amount} TRX | {$record->created_at}"
                : "🏆 Ticket: {$record->ticket_number} | Prize: {$record->prize_amount} TRX | {$record->created_at}";
        })->join("\n\n");

        $totalPrize = $winRecords->sum('prize_amount');

        $text = $isCn
            ? "🎉 我的中奖记录\n\n" .
              "总中奖次数：{$winRecords->count()}\n" .
              "总中奖金额：{$totalPrize} TRX\n\n" .
              $winList
            : "🎉 My Winning Records\n\n" .
              "Total Wins: {$winRecords->count()}\n" .
              "Total Prize: {$totalPrize} TRX\n\n" .
              $winList;

        return ['success' => true, 'message' => $text];
    }

    /**
     * 奖池命令
     */
    protected function handlePrizePool(int $chatId, bool $isCn): array
    {
        $config = $this->configService->getByTgChatId($chatId);
        if (!$config) {
            return [
                'success' => false,
                'message' => $isCn ? '群组未配置' : 'Group not configured',
            ];
        }

        $group = $this->groupService->getByConfigId($config->id);
        if (!$group) {
            return [
                'success' => false,
                'message' => $isCn ? '群组未找到' : 'Group not found',
            ];
        }

        $text = $isCn
            ? "🏆 当前奖池\n\n" .
              "总奖池：{$group->prize_pool} TRX\n" .
              "钱包地址：{$config->wallet_address}"
            : "🏆 Current Prize Pool\n\n" .
              "Total Pool: {$group->prize_pool} TRX\n" .
              "Wallet Address: {$config->wallet_address}";

        return ['success' => true, 'message' => $text];
    }

    /**
     * 最近中奖命令
     * @param int $chatId 群组ID
     * @param bool $isCn 是否中文
     * @param int $recordIndex 中奖记录索引（从0开始）
     * @param int $nodePage 节点列表页码（从1开始）
     * @return array
     */
    protected function handleRecentWins(int $chatId, bool $isCn, int $recordIndex = 0, int $nodePage = 1): array
    {
        $config = $this->configService->getByTgChatId($chatId);
        if (!$config) {
            return [
                'success' => false,
                'message' => $isCn ? '群组未配置' : 'Group not configured',
            ];
        }

        $group = $this->groupService->getByConfigId($config->id);
        if (!$group) {
            return [
                'success' => false,
                'message' => $isCn ? '群组未找到' : 'Group not found',
            ];
        }

        // 获取中奖记录总数
        $totalRecords = $this->prizeService->getGroupPrizeCount($group->id);

        if ($totalRecords == 0) {
            return [
                'success' => true,
                'message' => $isCn ? '暂无中奖记录' : 'No winning records yet',
            ];
        }

        $recordIndex = max(0, min($recordIndex, $totalRecords - 1));

        // 获取单条中奖记录
        $recentWins = $this->prizeService->getGroupRecentWinsPaginated($group->id, 1, $recordIndex);
        if ($recentWins->isEmpty()) {
            return [
                'success' => true,
                'message' => $isCn ? '暂无中奖记录' : 'No winning records yet',
            ];
        }

        /** @var \app\model\ModelTgPrizeRecord $record */
        $record = $recentWins->first();

        // 根据首尾节点ID查询中奖节点
        $firstNodeId = $record->winner_node_id_first;
        $lastNodeId = $record->winner_node_id_last;

        // 获取首尾中奖节点详情
        $firstNode = $this->nodeService->findById($firstNodeId);
        $lastNode = $this->nodeService->findById($lastNodeId);

        // 获取区间内所有节点（包含首尾和中间）
        $allNodes = $this->nodeService->getNodesBetween($firstNodeId, $lastNodeId);

        // 节点列表分页
        $nodesPerPage = 10;
        $totalNodes = $allNodes->count();
        $totalNodePages = max(1, ceil($totalNodes / $nodesPerPage));
        $nodePage = max(1, min($nodePage, $totalNodePages));
        $nodeOffset = ($nodePage - 1) * $nodesPerPage;
        $pageNodes = $allNodes->slice($nodeOffset, $nodesPerPage);

        // 计算中奖间隔（首尾之间的期数差）
        $prizeInterval = $lastNodeId - $firstNodeId;

        // 判断中奖人数（首尾是否同一人）
        $isSamePerson = $firstNode && $lastNode && $firstNode->player_address === $lastNode->player_address;
        $actualWinnerCount = $isSamePerson ? 1 : 2;

        $currentRecordNum = $recordIndex + 1;

        $text = $isCn
            ? "🎊 最近中奖记录\n\n"
            : "🎊 Recent Winners\n\n";

        if ($isCn) {
            $text .= "🏆 中奖流水号：{$record->prize_serial_no}\n";
            $text .= "   🎫 中奖票号：{$record->ticket_number}\n";
            $text .= "   📏 中奖间隔：{$prizeInterval} 期\n";
            $text .= "   👥 中奖人数：{$actualWinnerCount} 人\n";
            if ($firstNode) {
                $text .= "   💳 首中奖地址：{$firstNode->player_address}\n";
            }
            if ($lastNode && $firstNodeId != $lastNodeId) {
                $text .= "   💳 尾中奖地址：{$lastNode->player_address}\n";
            }
            $text .= "   💰 总奖金：{$record->prize_amount} TRX\n";
            $text .= "   🕐 时间：{$record->created_at}\n";
            $text .= "📋 节点列表（第 {$nodePage}/{$totalNodePages} 页，共 {$totalNodes} 个）：\n";
        } else {
            $text .= "🏆 Prize Serial: {$record->prize_serial_no}\n";
            $text .= "   🎫 Ticket: {$record->ticket_number}\n";
            $text .= "   📏 Interval: {$prizeInterval} rounds\n";
            $text .= "   👥 Winners: {$actualWinnerCount}\n";
            if ($firstNode) {
                $text .= "   💳 First Winner: {$firstNode->player_address}\n";
            }
            if ($lastNode && $firstNodeId != $lastNodeId) {
                $text .= "   💳 Last Winner: {$lastNode->player_address}\n";
            }
            $text .= "   💰 Prize: {$record->prize_amount} TRX\n";
            $text .= "   🕐 Time: {$record->created_at}\n";
            $text .= "📋 Nodes (Page {$nodePage}/{$totalNodePages}, Total {$totalNodes}):\n";
        }

        // 展示当前页的节点
        foreach ($pageNodes as $index => $node) {
            $walletSuffix = '...' . substr($node->player_address, -8);
            $num = $nodeOffset + $index + 1;
            $isWinner = ($node->id == $firstNodeId || $node->id == $lastNodeId);
            $statusIcon = $isWinner ? '🏆' : '⚪';
            $text .= "   {$num}. {$statusIcon} {$node->ticket_serial_no} | 🎫{$node->ticket_number} | 💳{$walletSuffix}\n";
        }

        // 构建分页按钮
        $buttons = [];
        $langFlag = $isCn ? '1' : '0';

        // 第一行：节点列表翻页
        $nodeButtons = [];
        if ($nodePage > 1) {
            $nodeButtons[] = [
                'text' => $isCn ? '⬅️ 上页节点' : '⬅️ Prev Nodes',
                'callback_data' => "wins_page:{$recordIndex}:" . ($nodePage - 1) . ":{$langFlag}",
            ];
        }
        if ($totalNodePages > 1) {
            $nodeButtons[] = [
                'text' => "{$nodePage}/{$totalNodePages}",
                'callback_data' => "wins_page:{$recordIndex}:{$nodePage}:{$langFlag}",
            ];
        }
        if ($nodePage < $totalNodePages) {
            $nodeButtons[] = [
                'text' => $isCn ? '下页节点 ➡️' : 'Next Nodes ➡️',
                'callback_data' => "wins_page:{$recordIndex}:" . ($nodePage + 1) . ":{$langFlag}",
            ];
        }
        if (!empty($nodeButtons)) {
            $buttons[] = $nodeButtons;
        }

        // 第二行：中奖记录切换
        $recordButtons = [];
        if ($recordIndex > 0) {
            $recordButtons[] = [
                'text' => $isCn ? '⏮️ 上一条' : '⏮️ Prev Record',
                'callback_data' => "wins_page:" . ($recordIndex - 1) . ":1:{$langFlag}",
            ];
        }
        if ($totalRecords > 1) {
            $recordButtons[] = [
                'text' => "{$currentRecordNum}/{$totalRecords}",
                'callback_data' => "wins_page:{$recordIndex}:1:{$langFlag}",
            ];
        }
        if ($recordIndex < $totalRecords - 1) {
            $recordButtons[] = [
                'text' => $isCn ? '下一条 ⏭️' : 'Next Record ⏭️',
                'callback_data' => "wins_page:" . ($recordIndex + 1) . ":1:{$langFlag}",
            ];
        }
        if (!empty($recordButtons)) {
            $buttons[] = $recordButtons;
        }

        $inlineKeyboard = !empty($buttons) ? $buttons : null;

        return [
            'success' => true,
            'message' => $text,
            'inline_keyboard' => $inlineKeyboard,
        ];
    }

    /**
     * 最近中奖分页回调处理（供TelegramService调用）
     * @param int $chatId 群组ID
     * @param bool $isCn 是否中文
     * @param int $recordIndex 中奖记录索引（从0开始）
     * @param int $nodePage 节点列表页码（从1开始）
     * @return array
     */
    public function handleRecentWinsCallback(int $chatId, bool $isCn, int $recordIndex, int $nodePage): array
    {
        return $this->handleRecentWins($chatId, $isCn, $recordIndex, $nodePage);
    }

    /**
     * 统计命令
     */
    protected function handleStats(int $chatId, bool $isCn): array
    {
        $config = $this->configService->getByTgChatId($chatId);
        if (!$config) {
            return [
                'success' => false,
                'message' => $isCn ? '群组未配置' : 'Group not configured',
            ];
        }

        $group = $this->groupService->getByConfigId($config->id);
        if (!$group) {
            return [
                'success' => false,
                'message' => $isCn ? '群组未找到' : 'Group not found',
            ];
        }

        // 获取活跃节点统计
        $activeNodes = $this->nodeService->getActiveNodes($group->id);
        $snakeLength = $activeNodes->count();

        // 获取蛇头票号
        $snakeHeadTicket = $isCn ? '暂无' : 'None';
        if ($snakeLength > 0) {
            /** @var \app\model\ModelTgSnakeNode $firstNode */
            $firstNode = $activeNodes->first();
            $snakeHeadTicket = $firstNode->ticket_number;
        }

        // 获取节点统计数据
        $nodeStats = $this->nodeService->getGroupStatistics($group->id);

        // 获取中奖统计数据
        $prizeStats = $this->prizeService->getGroupStatistics($group->id);

        $text = $isCn
            ? "📊 群组统计\n\n" .
              "当前蛇身长度：{$snakeLength}\n" .
              "当前蛇头：{$snakeHeadTicket}\n" .
              "总奖池：{$group->prize_pool_amount} TRX\n" .
              "钱包周期：#{$config->wallet_change_count}\n\n" .
              "参与玩家数：{$nodeStats['unique_players']}\n" .
              "总投注金额：{$nodeStats['total_amount']} TRX\n" .
              "总交易次数：{$nodeStats['total_nodes']}\n\n" .
              "总中奖次数：{$prizeStats['total_count']}\n" .
              "总派奖金额：{$prizeStats['total_prize_amount']} TRX"
            : "📊 Group Statistics\n\n" .
              "Current Snake Length: {$snakeLength}\n" .
              "Snake Head: {$snakeHeadTicket}\n" .
              "Prize Pool: {$group->prize_pool_amount} TRX\n" .
              "Wallet Cycle: #{$config->wallet_change_count}\n\n" .
              "Total Players: {$nodeStats['unique_players']}\n" .
              "Total Bet Amount: {$nodeStats['total_amount']} TRX\n" .
              "Total Transactions: {$nodeStats['total_nodes']}\n\n" .
              "Total Wins: {$prizeStats['total_count']}\n" .
              "Total Prizes: {$prizeStats['total_prize_amount']} TRX";

        return ['success' => true, 'message' => $text];
    }

    /**
     * 钱包变更命令（管理员）
     */
    protected function handleWalletChange(int $chatId, int $userId, array $params, bool $isCn): array
    {
        // 验证管理员权限
        if (!$this->isGroupAdmin($chatId, $userId)) {
            return [
                'success' => false,
                'message' => $isCn ? '❌ 只有管理员可以执行此操作' : '❌ Only administrators can perform this action',
            ];
        }

        if (count($params) < 2) {
            return [
                'success' => false,
                'message' => $isCn
                    ? '请提供新钱包地址和冷却时间（分钟）\n示例：/wallet_change TxxxNew... 60'
                    : 'Please provide new wallet address and cooldown minutes\nExample: /wallet_change TxxxNew... 60',
            ];
        }

        $newWalletAddress = $params[0];
        $cooldownMinutes = (int)$params[1];

        // 验证TRON钱包地址格式
        if (!TronWebHelper::isValidAddress($newWalletAddress)) {
            return [
                'success' => false,
                'message' => $isCn
                    ? '❌ 无效的TRON钱包地址格式'
                    : '❌ Invalid TRON wallet address format',
            ];
        }

        if ($cooldownMinutes < 1 || $cooldownMinutes > 1440) {
            return [
                'success' => false,
                'message' => $isCn
                    ? '❌ 冷却时间必须在1-1440分钟之间'
                    : '❌ Cooldown must be between 1-1440 minutes',
            ];
        }

        $config = $this->configService->getByTgChatId($chatId);
        if (!$config) {
            return [
                'success' => false,
                'message' => $isCn ? '群组未配置' : 'Group not configured',
            ];
        }

        $result = $this->configService->startWalletChange($config->id, $newWalletAddress, $cooldownMinutes);

        if ($result['success']) {
            return [
                'success' => true,
                'message' => $isCn
                    ? "✅ 钱包变更已启动\n\n" .
                      "新钱包地址：{$newWalletAddress}\n" .
                      "冷却时间：{$cooldownMinutes}分钟\n" .
                      "结束时间：{$result['end_at']}\n\n" .
                      "💡 冷却期间不接受新的投注，期满后自动完成变更"
                    : "✅ Wallet change initiated\n\n" .
                      "New Address: {$newWalletAddress}\n" .
                      "Cooldown: {$cooldownMinutes} minutes\n" .
                      "Ends at: {$result['end_at']}\n\n" .
                      "💡 No new bets during cooldown, change completes automatically",
            ];
        }

        return [
            'success' => false,
            'message' => $result['message'],
        ];
    }

    /**
     * 取消钱包变更命令（管理员）
     */
    protected function handleCancelWalletChange(int $chatId, int $userId, bool $isCn): array
    {
        // 验证管理员权限
        if (!$this->isGroupAdmin($chatId, $userId)) {
            return [
                'success' => false,
                'message' => $isCn ? '❌ 只有管理员可以执行此操作' : '❌ Only administrators can perform this action',
            ];
        }

        $config = $this->configService->getByTgChatId($chatId);
        if (!$config) {
            return [
                'success' => false,
                'message' => $isCn ? '群组未配置' : 'Group not configured',
            ];
        }

        $result = $this->configService->cancelWalletChange($config->id);

        if ($result['success']) {
            return [
                'success' => true,
                'message' => $isCn ? '✅ 钱包变更已取消' : '✅ Wallet change cancelled',
            ];
        }

        return [
            'success' => false,
            'message' => $result['message'],
        ];
    }

    /**
     * 群组配置命令（管理员）
     */
    protected function handleGroupConfig(int $chatId, int $userId, bool $isCn): array
    {
        // 验证管理员权限
        if (!$this->isGroupAdmin($chatId, $userId)) {
            return [
                'success' => false,
                'message' => $isCn ? '❌ 只有管理员可以查看群组配置' : '❌ Only administrators can view group configuration',
            ];
        }

        $config = $this->configService->getByTgChatId($chatId);
        if (!$config) {
            return [
                'success' => false,
                'message' => $isCn ? '群组未配置' : 'Group not configured',
            ];
        }

        // 计算平台手续费率（转换为百分比）
        $platformFeePercent = round($config->platform_fee_rate * 100, 2);

        $text = $isCn
            ? "⚙️ 群组配置\n\n" .
              "群组名称：{$config->tg_chat_title}\n" .
              "钱包地址：{$config->wallet_address}\n" .
              "固定投注金额：{$config->bet_amount} TRX\n" .
              "平台手续费率：{$platformFeePercent}%\n" .
              "钱包周期：第 {$config->wallet_change_count} 期\n" .
              "状态：" . ($config->status == 1 ? '✅ 启用' : '❌ 禁用')
            : "⚙️ Group Configuration\n\n" .
              "Group Name: {$config->tg_chat_title}\n" .
              "Wallet Address: {$config->wallet_address}\n" .
              "Fixed Bet Amount: {$config->bet_amount} TRX\n" .
              "Platform Fee Rate: {$platformFeePercent}%\n" .
              "Wallet Cycle: #{$config->wallet_change_count}\n" .
              "Status: " . ($config->status == 1 ? '✅ Enabled' : '❌ Disabled');

        return ['success' => true, 'message' => $text];
    }

    /**
     * 获取ID命令
     */
    protected function handleGetId(int $userId, bool $isCn): array
    {
        $text = $isCn
            ? "您的Telegram用户ID：{$userId}"
            : "Your Telegram User ID: {$userId}";

        return ['success' => true, 'message' => $text];
    }

    /**
     * 获取群ID命令
     */
    protected function handleGetGroupId(int $chatId, bool $isCn): array
    {
        $text = $isCn
            ? "当前群组聊天ID：{$chatId}"
            : "Current Group Chat ID: {$chatId}";

        return ['success' => true, 'message' => $text];
    }

    /**
     * 绑定租户ID命令
     * 注意：首次绑定时无需管理员权限，谁先绑定谁就是管理员
     */
    protected function handleBindTenant(int $chatId, int $userId, array $params, array $messageData, bool $isCn): array
    {
        // 验证参数
        if (empty($params[0])) {
            return [
                'success' => false,
                'message' => $isCn
                    ? "❌ 请提供租户ID\n用法：/绑定租户 租户ID"
                    : "❌ Please provide tenant ID\nUsage: /bind_tenant TENANT_ID",
            ];
        }

        $tenantId = trim($params[0]);

        try {
            // 1. 检查当前群组是否已有配置
            $config = $this->configService->getByTgChatId($chatId);

            // 2. 如果已有配置，需要管理员权限才能修改
            if ($config) {
                if (!TelegramBotHelper::checkAdmin($chatId, $userId)) {
                    return [
                        'success' => false,
                        'message' => $isCn
                            ? '❌ 群组已绑定租户，只有管理员可以修改'
                            : '❌ Group already bound, only administrators can modify',
                    ];
                }
            }

            // 3. 验证租户ID是否存在
            $tenant = \app\model\ModelTenant::where('tenant_id', $tenantId)->first();
            if (!$tenant) {
                return [
                    'success' => false,
                    'message' => $isCn
                        ? "❌ 租户ID不存在\n租户ID：{$tenantId}\n\n请检查租户ID是否正确"
                        : "❌ Tenant ID does not exist\nTenant ID: {$tenantId}\n\nPlease check if the tenant ID is correct",
                ];
            }

            // 4. 检查该租户ID是否已被其他群绑定
            $existingConfig = \app\model\ModelTgGameGroupConfig::where('tenant_id', $tenantId)
                ->where('tg_chat_id', '!=', $chatId)
                ->first();

            if ($existingConfig) {
                return [
                    'success' => false,
                    'message' => $isCn
                        ? "❌ 租户ID已被其他群绑定\n租户ID：{$tenantId}\n已绑定群组ID：{$existingConfig->tg_chat_id}\n\n一个租户ID只能绑定一个群组"
                        : "❌ Tenant ID is already bound to another group\nTenant ID: {$tenantId}\nBound Group ID: {$existingConfig->tg_chat_id}\n\nOne tenant ID can only be bound to one group",
                ];
            }

            // 5. 更新或创建配置
            if ($config) {
                // 更新租户ID
                $this->configService->updateConfig($config->id, [
                    'tenant_id' => $tenantId,
                ], 2);  // change_source = 2 (Telegram Bot)

                $message = $isCn
                    ? "✅ 租户ID已更新\n\n" .
                      "租户ID：{$tenantId}\n" .
                      "租户名称：{$tenant->company_name}\n" .
                      "群组ID：{$chatId}\n" .
                      "当前投注金额：{$config->bet_amount} TRX\n" .
                      "钱包地址：" . ($config->wallet_address ?: '未设置') . "\n\n" .
                      ($config->wallet_address ? "✅ 群组已配置完成，可以开始游戏" : "⚠️ 请继续设置收款钱包：/设置钱包 TRON地址")
                    : "✅ Tenant ID updated\n\n" .
                      "Tenant ID: {$tenantId}\n" .
                      "Tenant Name: {$tenant->company_name}\n" .
                      "Group ID: {$chatId}\n" .
                      "Current Bet Amount: {$config->bet_amount} TRX\n" .
                      "Wallet Address: " . ($config->wallet_address ?: 'Not set') . "\n\n" .
                      ($config->wallet_address ? "✅ Group configured, game is ready" : "⚠️ Please set wallet: /set_wallet TRON_ADDRESS");
            } else {
                // 创建新配置，并将执行绑定的用户设为首位管理员
                // 从messageData中获取群组名称，如果获取不到则使用默认值
                $chatTitle = $messageData['chat_title'] ?? 'Unknown';
                $newConfig = $this->configService->create([
                    'tenant_id' => $tenantId,
                    'tg_chat_id' => $chatId,
                    'tg_chat_title' => $chatTitle ?: 'Unknown', // 使用实际群组名称
                    'wallet_address' => '',
                    'bet_amount' => 5.0, // 默认5 TRX
                    'platform_fee_rate' => 0.10, // 默认10%
                    'wallet_change_count' => 0,
                    'wallet_change_status' => 1,
                    'telegram_admin_whitelist' => (string)$userId, // 将绑定者设为首位管理员
                    'status' => 0, // 初始状态为禁用，需要设置钱包后才能启用
                    'change_source' => 2,  // 来源：Telegram Bot
                ]);

                Log::info("租户绑定成功，用户自动成为管理员，已自动创建游戏群组和日志", [
                    'chat_id' => $chatId,
                    'user_id' => $userId,
                    'tenant_id' => $tenantId,
                    'config_id' => $newConfig->id,
                ]);

                $message = $isCn
                    ? "✅ 租户ID已绑定\n\n" .
                      "租户ID：{$tenantId}\n" .
                      "租户名称：{$tenant->company_name}\n" .
                      "群组ID：{$chatId}\n" .
                      "默认投注金额：5 TRX\n" .
                      "平台手续费：10%\n\n" .
                      "🎉 您已自动成为群组管理员！\n" .
                      "用户ID：{$userId}\n\n" .
                      "⚠️ 请继续执行以下步骤：\n" .
                      "1️⃣ 设置收款钱包：/设置钱包 TRON地址\n" .
                      "2️⃣ 设置投注金额（可选）：/设置投注 金额\n" .
                      "3️⃣ 添加其他管理员（可选）：/添加管理 @用户名"
                    : "✅ Tenant ID bound\n\n" .
                      "Tenant ID: {$tenantId}\n" .
                      "Tenant Name: {$tenant->company_name}\n" .
                      "Group ID: {$chatId}\n" .
                      "Default Bet Amount: 5 TRX\n" .
                      "Platform Fee: 10%\n\n" .
                      "🎉 You are now the group administrator!\n" .
                      "User ID: {$userId}\n\n" .
                      "⚠️ Please continue with these steps:\n" .
                      "1️⃣ Set wallet: /set_wallet TRON_ADDRESS\n" .
                      "2️⃣ Set bet amount (optional): /set_bet_amount AMOUNT\n" .
                      "3️⃣ Add other admins (optional): /add_admin @username";
            }

            return ['success' => true, 'message' => $message];

        } catch (\Throwable $e) {
            Log::error("绑定租户ID失败", [
                'chat_id' => $chatId,
                'user_id' => $userId,
                'tenant_id' => $tenantId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'message' => $isCn
                    ? "❌ 绑定失败：" . $e->getMessage()
                    : "❌ Binding failed: " . $e->getMessage(),
            ];
        }
    }

    /**
     * 设置收款钱包地址命令（管理员专用）
     */
    protected function handleSetWallet(int $chatId, int $userId, array $params, bool $isCn): array
    {
        // 验证管理员权限
        if (!TelegramBotHelper::checkAdmin($chatId, $userId)) {
            return [
                'success' => false,
                'message' => $isCn ? '❌ 只有管理员可以执行此操作' : '❌ Only administrators can perform this action',
            ];
        }

        // 验证参数
        if (empty($params[0])) {
            return [
                'success' => false,
                'message' => $isCn
                    ? "❌ 请提供TRON钱包地址\n用法：/设置钱包 TRON地址"
                    : "❌ Please provide TRON wallet address\nUsage: /set_wallet TRON_ADDRESS",
            ];
        }

        $walletAddress = trim($params[0]);

        // 验证TRON地址格式
        if (!preg_match('/^T[A-Za-z1-9]{33}$/', $walletAddress)) {
            return [
                'success' => false,
                'message' => $isCn
                    ? "❌ 无效的TRON钱包地址格式\n地址必须以T开头，长度为34位"
                    : "❌ Invalid TRON wallet address format\nAddress must start with T and be 34 characters long",
            ];
        }

        try {
            // 获取群组配置
            $config = $this->configService->getByTgChatId($chatId);

            if (!$config) {
                return [
                    'success' => false,
                    'message' => $isCn
                        ? "❌ 群组未配置，请先执行：/绑定租户 租户ID"
                        : "❌ Group not configured, please first execute: /bind_tenant TENANT_ID",
                ];
            }

            // 更新钱包地址
            $this->configService->updateConfig($config->id, [
                'wallet_address' => $walletAddress,
                'wallet_change_count' => $config->wallet_change_count + 1,
                'status' => 1, // 设置钱包后自动启用
            ], 2);  // change_source = 2 (Telegram Bot)

            // 初始化交易基准点：获取钱包最新的交易记录并保存，避免后续监听时处理历史交易
            $this->initializeTransactionBaseline($config->id, $walletAddress);

            $message = $isCn
                ? "✅ 收款钱包地址已设置\n\n" .
                  "钱包地址：<code>{$walletAddress}</code>\n" .
                  "钱包周期：第 " . ($config->wallet_change_count + 1) . " 期\n" .
                  "投注金额：{$config->bet_amount} TRX\n\n" .
                  "🎮 游戏已启动！\n" .
                  "💰 系统将每10秒监听此钱包的收款\n" .
                  "📢 群友可以开始投注了！\n\n" .
                  "👥 群友参与步骤：\n" .
                  "1️⃣ 绑定钱包：/绑定钱包 您的TRON地址\n" .
                  "2️⃣ 转账 {$config->bet_amount} TRX 到上面的钱包地址\n" .
                  "3️⃣ 等待系统通知投注成功"
                : "✅ Receive wallet address set\n\n" .
                  "Wallet Address: <code>{$walletAddress}</code>\n" .
                  "Wallet Cycle: #" . ($config->wallet_change_count + 1) . "\n" .
                  "Bet Amount: {$config->bet_amount} TRX\n\n" .
                  "🎮 Game started!\n" .
                  "💰 System will monitor this wallet every 10 seconds\n" .
                  "📢 Members can start betting now!\n\n" .
                  "👥 How to participate:\n" .
                  "1️⃣ Bind wallet: /bind_wallet YOUR_TRON_ADDRESS\n" .
                  "2️⃣ Transfer {$config->bet_amount} TRX to the wallet address above\n" .
                  "3️⃣ Wait for system notification of successful bet";

            return ['success' => true, 'message' => $message];

        } catch (\Throwable $e) {
            Log::error("设置钱包地址失败", [
                'chat_id' => $chatId,
                'user_id' => $userId,
                'wallet_address' => $walletAddress,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'message' => $isCn
                    ? "❌ 设置失败：" . $e->getMessage()
                    : "❌ Setup failed: " . $e->getMessage(),
            ];
        }
    }

    /**
     * 设置投注金额命令（管理员专用）
     */
    protected function handleSetBetAmount(int $chatId, int $userId, array $params, bool $isCn): array
    {
        // 验证管理员权限
        if (!TelegramBotHelper::checkAdmin($chatId, $userId)) {
            return [
                'success' => false,
                'message' => $isCn ? '❌ 只有管理员可以执行此操作' : '❌ Only administrators can perform this action',
            ];
        }

        // 验证参数
        if (empty($params[0])) {
            return [
                'success' => false,
                'message' => $isCn
                    ? "❌ 请提供投注金额\n用法：/设置投注 金额"
                    : "❌ Please provide bet amount\nUsage: /set_bet_amount AMOUNT",
            ];
        }

        $betAmount = floatval($params[0]);

        // 验证金额范围
        if ($betAmount < 0.1 || $betAmount > 10000) {
            return [
                'success' => false,
                'message' => $isCn
                    ? "❌ 投注金额必须在 0.1 - 10000 TRX 之间"
                    : "❌ Bet amount must be between 0.1 and 10000 TRX",
            ];
        }

        try {
            // 获取群组配置
            $config = $this->configService->getByTgChatId($chatId);

            if (!$config) {
                return [
                    'success' => false,
                    'message' => $isCn
                        ? "❌ 群组未配置，请先执行：/绑定租户 租户ID"
                        : "❌ Group not configured, please first execute: /bind_tenant TENANT_ID",
                ];
            }

            // 更新投注金额
            $this->configService->updateConfig($config->id, [
                'bet_amount' => $betAmount,
            ], 2);  // change_source = 2 (Telegram Bot)

            $message = $isCn
                ? "✅ 投注金额已更新\n\n" .
                  "新投注金额：{$betAmount} TRX\n" .
                  "钱包地址：" . ($config->wallet_address ?: '未设置') . "\n" .
                  "群组状态：" . ($config->status == 1 ? '✅ 启用' : '❌ 禁用')
                : "✅ Bet amount updated\n\n" .
                  "New Bet Amount: {$betAmount} TRX\n" .
                  "Wallet Address: " . ($config->wallet_address ?: 'Not set') . "\n" .
                  "Group Status: " . ($config->status == 1 ? '✅ Enabled' : '❌ Disabled');

            return ['success' => true, 'message' => $message];

        } catch (\Throwable $e) {
            Log::error("设置投注金额失败", [
                'chat_id' => $chatId,
                'bet_amount' => $betAmount,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => $isCn
                    ? "❌ 设置失败：" . $e->getMessage()
                    : "❌ Setup failed: " . $e->getMessage(),
            ];
        }
    }

    /**
     * 添加管理员到白名单命令（管理员专用）
     */
    protected function handleAddAdmin(int $chatId, int $userId, array $params, array $messageData, bool $isCn): array
    {
        // 验证管理员权限
        if (!TelegramBotHelper::checkAdmin($chatId, $userId)) {
            return [
                'success' => false,
                'message' => $isCn ? '❌ 只有管理员可以执行此操作' : '❌ Only administrators can perform this action',
            ];
        }

        try {
            // 获取群组配置
            $config = $this->configService->getByTgChatId($chatId);
            if (!$config) {
                return [
                    'success' => false,
                    'message' => $isCn
                        ? "❌ 群组未配置，请先执行：/绑定租户 租户ID"
                        : "❌ Group not configured, please first execute: /bind_tenant TENANT_ID",
                ];
            }

            // 获取目标用户ID
            $targetUserId = null;

            // 方式1：通过回复消息获取用户ID
            if (!empty($messageData['reply_to_message'])) {
                $targetUserId = $messageData['reply_to_message']['from']['id'] ?? null;
            }
            // 方式2：通过用户名参数（@username）
            elseif (!empty($params[0]) && str_starts_with($params[0], '@')) {
                $username = ltrim($params[0], '@');
                // 从绑定记录中查找用户ID
                $binding = $this->bindingService->getByUsername($config->id, $username);
                if ($binding) {
                    $targetUserId = $binding->tg_user_id;
                } else {
                    return [
                        'success' => false,
                        'message' => $isCn
                            ? "❌ 未找到用户 @{$username}\n该用户可能未在本群绑定钱包\n\n💡 请使用以下方式：\n1. 回复该用户消息后执行 /添加管理\n2. 直接使用用户ID：/添加管理 用户ID"
                            : "❌ User @{$username} not found\nThis user may not have bound wallet in this group\n\n💡 Please use:\n1. Reply to user message and execute /add_admin\n2. Use user ID directly: /add_admin USER_ID",
                    ];
                }
            }
            // 方式3：通过用户ID参数（数字）
            elseif (!empty($params[0])) {
                $targetUserId = intval($params[0]);
            }

            if (!$targetUserId) {
                return [
                    'success' => false,
                    'message' => $isCn
                        ? "❌ 请提供用户ID、@用户名或回复用户消息\n用法1：/添加管理 用户ID\n用法2：/添加管理 @用户名\n用法3：回复用户消息后执行 /添加管理"
                        : "❌ Please provide user ID, @username or reply to user message\nUsage 1: /add_admin USER_ID\nUsage 2: /add_admin @username\nUsage 3: Reply to user message and execute /add_admin",
                ];
            }

            // 添加到白名单
            if (!$config->addAdminToWhitelist($targetUserId)) {
                return [
                    'success' => false,
                    'message' => $isCn
                        ? "⚠️ 用户已在管理员白名单中\n用户ID：{$targetUserId}"
                        : "⚠️ User already in admin whitelist\nUser ID: {$targetUserId}",
                ];
            }

            // 通过Service保存并记录日志
            $this->configService->updateConfig($config->id, [
                'telegram_admin_whitelist' => $config->telegram_admin_whitelist,
            ], 2);  // change_source = 2 (Telegram Bot)

            $message = $isCn
                ? "✅ 已添加到管理员白名单\n\n" .
                  "用户ID：<code>{$targetUserId}</code>\n" .
                  "当前白名单人数：" . count($config->getAdminWhitelistArray()) . " 人\n\n" .
                  "💡 该用户现在可以使用所有管理员命令"
                : "✅ Added to admin whitelist\n\n" .
                  "User ID: <code>{$targetUserId}</code>\n" .
                  "Current whitelist count: " . count($config->getAdminWhitelistArray()) . " users\n\n" .
                  "💡 This user can now use all admin commands";

            return ['success' => true, 'message' => $message];

        } catch (\Throwable $e) {
            Log::error("添加管理员白名单失败", [
                'chat_id' => $chatId,
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => $isCn
                    ? "❌ 操作失败：" . $e->getMessage()
                    : "❌ Operation failed: " . $e->getMessage(),
            ];
        }
    }

    /**
     * 从白名单移除管理员命令（仅超级管理员可用）
     * 超级管理员：白名单中的第一个用户
     */
    protected function handleRemoveAdmin(int $chatId, int $userId, array $params, array $messageData, bool $isCn): array
    {
        // 验证管理员权限
        if (!TelegramBotHelper::checkAdmin($chatId, $userId)) {
            return [
                'success' => false,
                'message' => $isCn ? '❌ 只有管理员可以执行此操作' : '❌ Only administrators can perform this action',
            ];
        }

        try {
            // 获取群组配置
            $config = $this->configService->getByTgChatId($chatId);
            if (!$config) {
                return [
                    'success' => false,
                    'message' => $isCn
                        ? "❌ 群组未配置"
                        : "❌ Group not configured",
                ];
            }

            // 验证是否为超级管理员（白名单中的第一个用户）
            $whitelist = $config->getAdminWhitelistArray();
            if (empty($whitelist) || $whitelist[0] != $userId) {
                return [
                    'success' => false,
                    'message' => $isCn
                        ? "❌ 只有超级管理员（首位管理员）可以移除其他管理员"
                        : "❌ Only the super admin (first admin) can remove other administrators",
                ];
            }

            // 获取目标用户ID
            $targetUserId = null;

            // 方式1：通过回复消息获取用户ID
            if (!empty($messageData['reply_to_message'])) {
                $targetUserId = $messageData['reply_to_message']['from']['id'] ?? null;
            }
            // 方式2：通过用户名参数（@username）
            elseif (!empty($params[0]) && str_starts_with($params[0], '@')) {
                $username = ltrim($params[0], '@');
                // 从绑定记录中查找用户ID
                $binding = $this->bindingService->getByUsername($config->id, $username);
                if ($binding) {
                    $targetUserId = $binding->tg_user_id;
                } else {
                    return [
                        'success' => false,
                        'message' => $isCn
                            ? "❌ 未找到用户 @{$username}\n该用户可能未在本群绑定钱包\n\n💡 请使用以下方式：\n1. 回复该用户消息后执行 /移除管理\n2. 直接使用用户ID：/移除管理 用户ID"
                            : "❌ User @{$username} not found\nThis user may not have bound wallet in this group\n\n💡 Please use:\n1. Reply to user message and execute /remove_admin\n2. Use user ID directly: /remove_admin USER_ID",
                    ];
                }
            }
            // 方式3：通过用户ID参数（数字）
            elseif (!empty($params[0])) {
                $targetUserId = intval($params[0]);
            }

            if (!$targetUserId) {
                return [
                    'success' => false,
                    'message' => $isCn
                        ? "❌ 请提供用户ID、@用户名或回复用户消息\n用法1：/移除管理 用户ID\n用法2：/移除管理 @用户名\n用法3：回复用户消息后执行 /移除管理"
                        : "❌ Please provide user ID, @username or reply to user message\nUsage 1: /remove_admin USER_ID\nUsage 2: /remove_admin @username\nUsage 3: Reply to user message and execute /remove_admin",
                ];
            }

            // 从白名单移除
            if (!$config->removeAdminFromWhitelist($targetUserId)) {
                return [
                    'success' => false,
                    'message' => $isCn
                        ? "⚠️ 用户不在管理员白名单中\n用户ID：{$targetUserId}"
                        : "⚠️ User not in admin whitelist\nUser ID: {$targetUserId}",
                ];
            }

            // 通过Service保存并记录日志
            $this->configService->updateConfig($config->id, [
                'telegram_admin_whitelist' => $config->telegram_admin_whitelist,
            ], 2);  // change_source = 2 (Telegram Bot)

            $message = $isCn
                ? "✅ 已从管理员白名单移除\n\n" .
                  "用户ID：<code>{$targetUserId}</code>\n" .
                  "当前白名单人数：" . count($config->getAdminWhitelistArray()) . " 人"
                : "✅ Removed from admin whitelist\n\n" .
                  "User ID: <code>{$targetUserId}</code>\n" .
                  "Current whitelist count: " . count($config->getAdminWhitelistArray()) . " users";

            return ['success' => true, 'message' => $message];

        } catch (\Throwable $e) {
            Log::error("移除管理员白名单失败", [
                'chat_id' => $chatId,
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => $isCn
                    ? "❌ 操作失败：" . $e->getMessage()
                    : "❌ Operation failed: " . $e->getMessage(),
            ];
        }
    }

    /**
     * 查看管理员白名单命令（管理员专用）
     */
    protected function handleListAdmins(int $chatId, int $userId, bool $isCn): array
    {
        // 验证管理员权限
        if (!TelegramBotHelper::checkAdmin($chatId, $userId)) {
            return [
                'success' => false,
                'message' => $isCn ? '❌ 只有管理员可以执行此操作' : '❌ Only administrators can perform this action',
            ];
        }

        try {
            // 获取群组配置
            $config = $this->configService->getByTgChatId($chatId);
            if (!$config) {
                return [
                    'success' => false,
                    'message' => $isCn
                        ? "❌ 群组未配置"
                        : "❌ Group not configured",
                ];
            }

            $whitelist = $config->getAdminWhitelistArray();

            if (empty($whitelist)) {
                return [
                    'success' => true,
                    'message' => $isCn
                        ? "📋 管理员白名单\n\n⚠️ 白名单为空\n\n💡 使用 /添加管理 添加管理员"
                        : "📋 Admin Whitelist\n\n⚠️ Whitelist is empty\n\n💡 Use /add_admin to add administrators",
                ];
            }

            $message = $isCn
                ? "📋 管理员白名单\n\n" .
                  "总计：" . count($whitelist) . " 人\n\n" .
                  "用户ID列表：\n"
                : "📋 Admin Whitelist\n\n" .
                  "Total: " . count($whitelist) . " users\n\n" .
                  "User ID List:\n";

            foreach ($whitelist as $index => $adminId) {
                $message .= ($index + 1) . ". <code>{$adminId}</code>\n";
            }

            $message .= "\n💡 ";
            $message .= $isCn
                ? "使用 /添加管理 添加 | 使用 /移除管理 移除"
                : "Use /add_admin to add | Use /remove_admin to remove";

            return ['success' => true, 'message' => $message];

        } catch (\Throwable $e) {
            Log::error("查看管理员白名单失败", [
                'chat_id' => $chatId,
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => $isCn
                    ? "❌ 操作失败：" . $e->getMessage()
                    : "❌ Operation failed: " . $e->getMessage(),
            ];
        }
    }

    /**
     * 未知命令
     */
    protected function handleUnknown(bool $isCn): array
    {
        return [
            'success' => false,
            'message' => $isCn
                ? '未知命令，请使用 /help 查看命令列表'
                : 'Unknown command, use /help to see command list',
        ];
    }

    /**
     * 初始化交易基准点
     * 设置钱包时获取最新的交易记录并保存到数据库，作为后续监听的起点
     * 避免处理设置钱包之前的历史交易
     *
     * @param int $groupId 群组配置ID
     * @param string $walletAddress 钱包地址
     */
    protected function initializeTransactionBaseline(int $groupId, string $walletAddress): void
    {
        try {
            // 获取钱包最新的交易记录（只取最近1条TRX转账）
            $transactions = $this->tronHelper->getTransactionHistory($walletAddress, 0, 10);

            if (empty($transactions)) {
                Log::info("初始化交易基准点：钱包无历史交易", [
                    'group_id' => $groupId,
                    'wallet_address' => $walletAddress,
                ]);
                return;
            }

            // 取最新的一条交易作为基准点
            $latestTx = $transactions[0];

            // 记录到交易日志表（标记为已处理，不触发游戏逻辑）
            // processed = 1 表示已处理，避免被补偿机制重新处理
            // is_valid = 0 表示无效交易（基准点不参与游戏）
            $this->tronMonitorService->logTransaction([
                'group_id' => $groupId,
                'tx_hash' => $latestTx['tx_hash'],
                'from_address' => $latestTx['from_address'],
                'to_address' => $latestTx['to_address'],
                'amount' => $latestTx['amount'],
                'transaction_type' => 1, // 入账
                'block_height' => $latestTx['block_height'],
                'block_timestamp' => $latestTx['block_timestamp'],
                'status' => $latestTx['status'],
                'is_valid' => 0,  // 基准点交易标记为无效，不参与游戏
                'invalid_reason' => '初始化基准点交易，不参与游戏',
                'processed' => 1, // 标记为已处理，避免被补偿机制重新处理
            ]);

            Log::info("初始化交易基准点成功", [
                'group_id' => $groupId,
                'wallet_address' => $walletAddress,
                'baseline_tx_hash' => $latestTx['tx_hash'],
                'baseline_block_height' => $latestTx['block_height'],
            ]);

        } catch (\Throwable $e) {
            // 初始化失败不影响主流程，只记录日志
            Log::warning("初始化交易基准点失败: " . $e->getMessage(), [
                'group_id' => $groupId,
                'wallet_address' => $walletAddress,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
