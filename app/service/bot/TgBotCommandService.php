<?php

namespace app\service\bot;

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
                default => $this->handleUnknown($command === 'cn' . ucfirst($command)),
            };
        } catch (\Throwable $e) {
            Log::error("处理命令失败: {$command}", [
                'error' => $e->getMessage(),
                'chat_id' => $chatId,
                'user_id' => $userId,
            ]);
            return [
                'success' => false,
                'message' => $command === 'cn' . ucfirst($command) ? '命令处理失败，请稍后重试' : 'Command processing failed, please try again later',
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

        // TODO: 验证TRON钱包地址格式

        $config = $this->configService->getByTgChatId($chatId);
        if (!$config) {
            return [
                'success' => false,
                'message' => $isCn ? '群组未配置' : 'Group not configured',
            ];
        }

        $result = $this->bindingService->bindWallet($config->id, $userId, $username, $walletAddress);

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
        // TODO: 实现获取用户票号逻辑
        return [
            'success' => false,
            'message' => $isCn ? '功能开发中' : 'Feature under development',
        ];
    }

    /**
     * 我的中奖命令
     */
    protected function handleMyWins(int $chatId, int $userId, bool $isCn): array
    {
        // TODO: 实现获取用户中奖记录逻辑
        return [
            'success' => false,
            'message' => $isCn ? '功能开发中' : 'Feature under development',
        ];
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
        // TODO: 实现获取最近中奖记录逻辑
        return [
            'success' => false,
            'message' => $isCn ? '功能开发中' : 'Feature under development',
        ];
    }

    /**
     * 统计命令
     */
    protected function handleStats(int $chatId, bool $isCn): array
    {
        // TODO: 实现获取群组统计逻辑
        return [
            'success' => false,
            'message' => $isCn ? '功能开发中' : 'Feature under development',
        ];
    }

    /**
     * 钱包变更命令（管理员）
     */
    protected function handleWalletChange(int $chatId, int $userId, array $params, bool $isCn): array
    {
        // TODO: 验证管理员权限
        // TODO: 实现钱包变更逻辑
        return [
            'success' => false,
            'message' => $isCn ? '功能开发中' : 'Feature under development',
        ];
    }

    /**
     * 取消钱包变更命令（管理员）
     */
    protected function handleCancelWalletChange(int $chatId, int $userId, bool $isCn): array
    {
        // TODO: 验证管理员权限
        // TODO: 实现取消钱包变更逻辑
        return [
            'success' => false,
            'message' => $isCn ? '功能开发中' : 'Feature under development',
        ];
    }

    /**
     * 群组配置命令（管理员）
     */
    protected function handleGroupConfig(int $chatId, int $userId, bool $isCn): array
    {
        // TODO: 验证管理员权限
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
