<?php

namespace app\service\bot;

use app\lib\helper\TelegramBotHelper;
use app\lib\helper\TronWebHelper;
use app\service\TgGameGroupConfigService;
use app\service\TgGameGroupService;
use app\service\TgPlayerWalletBindingService;
use app\service\TgPrizeService;
use app\service\TgSnakeNodeService;
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

        $text = $isCn
            ? "🐍 贪吃蛇链上游戏规则\n\n" .
              "【游戏机制】\n" .
              "• 最小投注：{$config->min_bet_amount} TRX\n" .
              "• 匹配位数：{$config->prize_match_count}\n" .
              "• 蛇头票号：{$config->snake_head_ticket}\n\n" .
              "【中奖规则】\n" .
              "• 完全匹配（Jackpot）：蛇头与蛇身任意节点完全匹配\n" .
              "• 范围匹配：蛇头与蛇身节点部分匹配（前N位）\n\n" .
              "【奖金分配】\n" .
              "• Jackpot：{$config->prize_ratio_jackpot}%\n" .
              "• 范围匹配：{$config->prize_ratio_range_match}%\n" .
              "• 平台费：{$config->prize_ratio_platform}%\n\n" .
              "【参与方式】\n" .
              "1. 绑定钱包：/bind_wallet YOUR_ADDRESS\n" .
              "2. 向群组钱包转账参与游戏\n" .
              "3. 等待区块确认并生成票号\n" .
              "4. 系统自动检测中奖并派发奖金"
            : "🐍 Snake Chain Game Rules\n\n" .
              "【Game Mechanics】\n" .
              "• Min Bet: {$config->min_bet_amount} TRX\n" .
              "• Match Digits: {$config->prize_match_count}\n" .
              "• Snake Head Ticket: {$config->snake_head_ticket}\n\n" .
              "【Winning Rules】\n" .
              "• Perfect Match (Jackpot): Snake head completely matches any body node\n" .
              "• Range Match: Snake head partially matches body nodes (first N digits)\n\n" .
              "【Prize Distribution】\n" .
              "• Jackpot: {$config->prize_ratio_jackpot}%\n" .
              "• Range Match: {$config->prize_ratio_range_match}%\n" .
              "• Platform Fee: {$config->prize_ratio_platform}%\n\n" .
              "【How to Participate】\n" .
              "1. Bind wallet: /bind_wallet YOUR_ADDRESS\n" .
              "2. Transfer TRX to group wallet\n" .
              "3. Wait for block confirmation and ticket generation\n" .
              "4. System automatically detects wins and distributes prizes";

        return ['success' => true, 'message' => $text];
    }

    /**
     * 蛇身命令
     */
    protected function handleSnake(int $chatId, bool $isCn): array
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

        $snake = $this->groupService->getCurrentSnake($group->id);
        $snakeCount = count($snake);

        $text = $isCn
            ? "🐍 当前蛇身状态\n\n" .
              "蛇身长度：{$snakeCount} 节\n" .
              "蛇头票号：{$config->snake_head_ticket}\n\n" .
              "最近节点（最多显示10个）：\n"
            : "🐍 Current Snake Status\n\n" .
              "Snake Length: {$snakeCount} nodes\n" .
              "Snake Head Ticket: {$config->snake_head_ticket}\n\n" .
              "Recent Nodes (max 10):\n";

        $recentNodes = array_slice($snake, 0, 10);
        foreach ($recentNodes as $index => $node) {
            $text .= ($index + 1) . ". " . $node['ticket'] . " ({$node['amount']} TRX)\n";
        }

        return ['success' => true, 'message' => $text];
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
                ? "票号：{$node->ticket_number} | 投注：{$node->bet_amount} TRX | 位置：#{$node->position}"
                : "Ticket: {$node->ticket_number} | Bet: {$node->bet_amount} TRX | Position: #{$node->position}";
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
            $typeText = $isCn
                ? ($record->prize_type == 1 ? 'Jackpot' : '范围匹配')
                : ($record->prize_type == 1 ? 'Jackpot' : 'Range Match');

            return $isCn
                ? "🏆 {$typeText} | 票号：{$record->winning_ticket} | 奖金：{$record->prize_amount} TRX | {$record->created_at}"
                : "🏆 {$typeText} | Ticket: {$record->winning_ticket} | Prize: {$record->prize_amount} TRX | {$record->created_at}";
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
     */
    protected function handleRecentWins(int $chatId, bool $isCn): array
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

        // 获取最近的中奖记录（最近5条）
        $recentWins = $this->prizeService->getGroupRecentWins($group->id, 5);

        if ($recentWins->isEmpty()) {
            return [
                'success' => true,
                'message' => $isCn ? '暂无中奖记录' : 'No winning records yet',
            ];
        }

        $winList = $recentWins->map(function ($record) use ($isCn) {
            $typeText = $isCn
                ? ($record->prize_type == 1 ? 'Jackpot' : '范围匹配')
                : ($record->prize_type == 1 ? 'Jackpot' : 'Range Match');

            $username = $record->winner_username ?: 'User#' . $record->winner_tg_user_id;

            return $isCn
                ? "🏆 {$typeText}\n" .
                  "   中奖用户：@{$username}\n" .
                  "   票号：{$record->winning_ticket}\n" .
                  "   奖金：{$record->prize_amount} TRX\n" .
                  "   时间：{$record->created_at}"
                : "🏆 {$typeText}\n" .
                  "   Winner: @{$username}\n" .
                  "   Ticket: {$record->winning_ticket}\n" .
                  "   Prize: {$record->prize_amount} TRX\n" .
                  "   Time: {$record->created_at}";
        })->join("\n\n");

        $text = $isCn
            ? "🎊 最近中奖记录\n\n{$winList}"
            : "🎊 Recent Winners\n\n{$winList}";

        return ['success' => true, 'message' => $text];
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

        // 获取群组统计数据
        $stats = $this->groupService->getGroupStatistics($group->id);

        $text = $isCn
            ? "📊 群组统计\n\n" .
              "当前蛇身长度：{$stats['snake_length']}\n" .
              "当前蛇头：{$stats['snake_head_ticket']}\n" .
              "总奖池：{$group->prize_pool} TRX\n" .
              "钱包周期：#{$group->current_wallet_cycle}\n\n" .
              "参与玩家数：{$stats['total_players']}\n" .
              "总投注金额：{$stats['total_bet_amount']} TRX\n" .
              "总交易次数：{$stats['total_transactions']}\n\n" .
              "Jackpot中奖次数：{$stats['jackpot_wins']}\n" .
              "范围匹配次数：{$stats['range_wins']}\n" .
              "总派奖金额：{$stats['total_prize_amount']} TRX"
            : "📊 Group Statistics\n\n" .
              "Current Snake Length: {$stats['snake_length']}\n" .
              "Snake Head: {$stats['snake_head_ticket']}\n" .
              "Prize Pool: {$group->prize_pool} TRX\n" .
              "Wallet Cycle: #{$group->current_wallet_cycle}\n\n" .
              "Total Players: {$stats['total_players']}\n" .
              "Total Bet Amount: {$stats['total_bet_amount']} TRX\n" .
              "Total Transactions: {$stats['total_transactions']}\n\n" .
              "Jackpot Wins: {$stats['jackpot_wins']}\n" .
              "Range Wins: {$stats['range_wins']}\n" .
              "Total Prizes: {$stats['total_prize_amount']} TRX";

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

        $text = $isCn
            ? "⚙️ 群组配置\n\n" .
              "群组名称：{$config->tg_group_name}\n" .
              "钱包地址：{$config->wallet_address}\n" .
              "最小投注：{$config->min_bet_amount} TRX\n" .
              "匹配位数：{$config->prize_match_count}\n" .
              "蛇头票号：{$config->snake_head_ticket}\n" .
              "状态：" . ($config->status == 1 ? '启用' : '禁用')
            : "⚙️ Group Configuration\n\n" .
              "Group Name: {$config->tg_group_name}\n" .
              "Wallet Address: {$config->wallet_address}\n" .
              "Min Bet: {$config->min_bet_amount} TRX\n" .
              "Match Digits: {$config->prize_match_count}\n" .
              "Snake Head Ticket: {$config->snake_head_ticket}\n" .
              "Status: " . ($config->status == 1 ? 'Enabled' : 'Disabled');

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
}
