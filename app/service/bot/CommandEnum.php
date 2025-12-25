<?php

namespace app\service\bot;

/**
 * Telegram贪吃蛇游戏命令枚举
 */
class CommandEnum
{
    public const TELEGRAM_COMMAND_RUN_QUEUE_NAME = 'telegram-command-run-queue';
    public const TELEGRAM_NOTICE_QUEUE_NAME = 'telegram-notice-queue';

    // 英文指令集
    public const COMMAND_SET = [
        'help'                 => 'Help',
        'start'                => 'Start',
        'rules'                => 'Rules',
        'snake'                => 'Snake',
        'bind_wallet'          => 'BindWallet',
        'unbind_wallet'        => 'UnbindWallet',
        'my_wallet'            => 'MyWallet',
        'my_tickets'           => 'MyTickets',
        'my_wins'              => 'MyWins',
        'prize_pool'           => 'PrizePool',
        'recent_wins'          => 'RecentWins',
        'stats'                => 'Stats',
        'wallet_change'        => 'WalletChange',
        'cancel_wallet_change' => 'CancelWalletChange',
        'group_config'         => 'GroupConfig',
        'get_id'               => 'GetId',
        'get_group_id'         => 'GetGroupId',
        // 管理员初始化指令
        'bind_tenant'          => 'BindTenant',
        'set_wallet'           => 'SetWallet',
        'set_bet_amount'       => 'SetBetAmount',
        // 管理员白名单管理
        'add_admin'            => 'AddAdmin',
        'remove_admin'         => 'RemoveAdmin',
        'list_admins'          => 'ListAdmins',
    ];

    // 命令描述（英文）
    public static array $commandDescMap = [
        'help'                 => "<blockquote>Show all available commands\n[Example] /help</blockquote>",
        'start'                => "<blockquote>Get started with Snake Chain Game\n[Example] /start</blockquote>",
        'rules'                => "<blockquote>View game rules\n[Example] /rules</blockquote>",
        'snake'                => "<blockquote>View current snake body\n[Example] /snake</blockquote>",
        'bind_wallet'          => "<blockquote>Bind your TRON wallet address\n[Example] /bind_wallet TRX_ADDRESS\n[Param] wallet_address - Your TRON wallet address</blockquote>",
        'unbind_wallet'        => "<blockquote>Unbind your wallet\n[Example] /unbind_wallet</blockquote>",
        'my_wallet'            => "<blockquote>View your wallet binding\n[Example] /my_wallet</blockquote>",
        'my_tickets'           => "<blockquote>View your ticket numbers\n[Example] /my_tickets</blockquote>",
        'my_wins'              => "<blockquote>View your winning records\n[Example] /my_wins</blockquote>",
        'prize_pool'           => "<blockquote>View current prize pool amount\n[Example] /prize_pool</blockquote>",
        'recent_wins'          => "<blockquote>View recent winning records\n[Example] /recent_wins</blockquote>",
        'stats'                => "<blockquote>View group statistics\n[Example] /stats</blockquote>",
        'wallet_change'        => "<blockquote>[Admin Only] Initiate wallet change\n[Example] /wallet_change NEW_WALLET_ADDRESS COOLDOWN_MINUTES\n[Param] new_wallet_address - New TRON wallet address\n[Param] cooldown_minutes - Cooldown period in minutes (1-1440)</blockquote>",
        'cancel_wallet_change' => "<blockquote>[Admin Only] Cancel wallet change\n[Example] /cancel_wallet_change</blockquote>",
        'group_config'         => "<blockquote>[Admin Only] View group configuration\n[Example] /group_config</blockquote>",
        'get_id'               => "<blockquote>Get your Telegram user ID\n[Example] /get_id</blockquote>",
        'get_group_id'         => "<blockquote>Get current group chat ID\n[Example] /get_group_id</blockquote>",
        // 管理员初始化指令
        'bind_tenant'          => "<blockquote>[Admin Only] Bind tenant ID to this group\n[Example] /bind_tenant TENANT_ID\n[Param] tenant_id - Tenant ID</blockquote>",
        'set_wallet'           => "<blockquote>[Admin Only] Set receive wallet address\n[Example] /set_wallet TRON_ADDRESS\n[Param] wallet_address - TRON wallet address for receiving bets</blockquote>",
        'set_bet_amount'       => "<blockquote>[Admin Only] Set bet amount\n[Example] /set_bet_amount 5\n[Param] amount - Bet amount in TRX (default: 5)</blockquote>",
        // 管理员白名单管理
        'add_admin'            => "<blockquote>[Admin Only] Add user to admin whitelist\n[Example] /add_admin @username\n[Example] /add_admin (reply to user message)\n[Param] username - Username or reply to message</blockquote>",
        'remove_admin'         => "<blockquote>[Admin Only] Remove user from admin whitelist\n[Example] /remove_admin @username\n[Example] /remove_admin (reply to user message)\n[Param] username - Username or reply to message</blockquote>",
        'list_admins'          => "<blockquote>[Admin Only] List admin whitelist\n[Example] /list_admins</blockquote>",
    ];

    // 中文指令集
    public const COMMAND_SET_CN = [
        '帮助'     => 'cnHelp',
        '开始'     => 'cnStart',
        '规则'     => 'cnRules',
        '蛇身'     => 'cnSnake',
        '绑定钱包' => 'cnBindWallet',
        '解绑钱包' => 'cnUnbindWallet',
        '我的钱包' => 'cnMyWallet',
        '我的票号' => 'cnMyTickets',
        '我的中奖' => 'cnMyWins',
        '奖池'     => 'cnPrizePool',
        '最近中奖' => 'cnRecentWins',
        '统计'     => 'cnStats',
        '钱包变更' => 'cnWalletChange',
        '取消变更' => 'cnCancelWalletChange',
        '群组配置' => 'cnGroupConfig',
        '获取ID'   => 'cnGetId',
        '获取群ID' => 'cnGetGroupId',
        // 管理员初始化指令
        '绑定租户' => 'cnBindTenant',
        '设置钱包' => 'cnSetWallet',
        '设置投注' => 'cnSetBetAmount',
        // 管理员白名单管理
        '添加管理' => 'cnAddAdmin',
        '移除管理' => 'cnRemoveAdmin',
        '管理列表' => 'cnListAdmins',
    ];

    // 命令描述（中文）
    public static array $commandDescCnMap = [
        '帮助'     => "<blockquote>显示所有可用命令\n[示例] /帮助</blockquote>",
        '开始'     => "<blockquote>开始游戏说明\n[示例] /开始</blockquote>",
        '规则'     => "<blockquote>查看游戏规则\n[示例] /规则</blockquote>",
        '蛇身'     => "<blockquote>查看当前蛇身\n[示例] /蛇身</blockquote>",
        '绑定钱包' => "<blockquote>绑定您的TRON钱包地址\n[示例] /绑定钱包 TRX地址\n[参数] wallet_address - 您的TRON钱包地址</blockquote>",
        '解绑钱包' => "<blockquote>解除钱包绑定\n[示例] /解绑钱包</blockquote>",
        '我的钱包' => "<blockquote>查看钱包绑定\n[示例] /我的钱包</blockquote>",
        '我的票号' => "<blockquote>查看我的票号\n[示例] /我的票号</blockquote>",
        '我的中奖' => "<blockquote>查看我的中奖记录\n[示例] /我的中奖</blockquote>",
        '奖池'     => "<blockquote>查看当前奖池金额\n[示例] /奖池</blockquote>",
        '最近中奖' => "<blockquote>查看最近中奖记录\n[示例] /最近中奖</blockquote>",
        '统计'     => "<blockquote>查看群组统计\n[示例] /统计</blockquote>",
        '钱包变更' => "<blockquote>[仅管理员] 发起钱包变更\n[示例] /钱包变更 新钱包地址 冷却分钟数\n[参数] new_wallet_address - 新的TRON钱包地址\n[参数] cooldown_minutes - 冷却期分钟数(1-1440)</blockquote>",
        '取消变更' => "<blockquote>[仅管理员] 取消钱包变更\n[示例] /取消变更</blockquote>",
        '群组配置' => "<blockquote>[仅管理员] 查看群组配置\n[示例] /群组配置</blockquote>",
        '获取ID'   => "<blockquote>获取您的Telegram用户ID\n[示例] /获取ID</blockquote>",
        '获取群ID' => "<blockquote>获取当前群组聊天ID\n[示例] /获取群ID</blockquote>",
        // 管理员初始化指令
        '绑定租户' => "<blockquote>[仅管理员] 绑定租户ID到此群组\n[示例] /绑定租户 租户ID\n[参数] tenant_id - 租户ID</blockquote>",
        '设置钱包' => "<blockquote>[仅管理员] 设置收款钱包地址\n[示例] /设置钱包 TRON地址\n[参数] wallet_address - 用于接收投注的TRON钱包地址</blockquote>",
        '设置投注' => "<blockquote>[仅管理员] 设置投注金额\n[示例] /设置投注 5\n[参数] amount - TRX投注金额(默认:5)</blockquote>",
        // 管理员白名单管理
        '添加管理' => "<blockquote>[仅管理员] 添加用户到管理员白名单\n[示例] /添加管理 @用户名\n[示例] /添加管理 (回复用户消息)\n[参数] username - 用户名或回复消息</blockquote>",
        '移除管理' => "<blockquote>[仅管理员] 从管理员白名单移除用户\n[示例] /移除管理 @用户名\n[示例] /移除管理 (回复用户消息)\n[参数] username - 用户名或回复消息</blockquote>",
        '管理列表' => "<blockquote>[仅管理员] 查看管理员白名单\n[示例] /管理列表</blockquote>",
    ];

    /**
     * 判断是否是命令
     */
    public static function isCommand(string $command): bool
    {
        $command_set_cn_keys = array_keys(self::COMMAND_SET_CN);
        $command_set_keys = array_keys(self::COMMAND_SET);

        // 对中文命令进行大小写不敏感的匹配
        $commandLower = mb_strtolower(trim($command), 'UTF-8');
        $command_set_cn_keys_lower = array_map(function($key) {
            return mb_strtolower($key, 'UTF-8');
        }, $command_set_cn_keys);

        return in_array($commandLower, $command_set_cn_keys_lower, true)
            || in_array(strtolower(trim($command)), $command_set_keys, true);
    }

    /**
     * 获取命令对应的方法名
     */
    public static function getCommand(string $command): string
    {
        // 对中文命令进行大小写不敏感的匹配
        $commandLower = mb_strtolower(trim($command), 'UTF-8');

        // 遍历中文命令集，进行大小写不敏感的匹配
        foreach (self::COMMAND_SET_CN as $key => $value) {
            if (mb_strtolower($key, 'UTF-8') === $commandLower) {
                return $value;
            }
        }

        // 英文命令匹配（已经是小写的）
        $commandLowerEn = strtolower(trim($command));
        $command_set_keys = array_keys(self::COMMAND_SET);
        if (in_array($commandLowerEn, $command_set_keys, true)) {
            return self::COMMAND_SET[$commandLowerEn];
        }

        return '';
    }

    /**
     * 获取帮助信息
     */
    public static function getHelpReply(bool $isCn = false): array
    {
        $reply = [];
        if ($isCn) {
            $reply[] = '***** 贪吃蛇链上游戏 命令列表 *****';
            $reply[] = '';
            $reply[] = '【玩家命令】';
            $playerCommands = ['帮助', '开始', '规则', '蛇身', '绑定钱包', '解绑钱包', '我的钱包', '我的票号', '我的中奖', '奖池', '最近中奖', '统计'];
            foreach ($playerCommands as $key) {
                $reply[] = '/' . $key;
                $reply[] = self::getCommandDesc($key, true);
            }
            $reply[] = '';
            $reply[] = '【管理员命令】';
            $adminCommands = ['钱包变更', '取消变更', '群组配置'];
            foreach ($adminCommands as $key) {
                $reply[] = '/' . $key;
                $reply[] = self::getCommandDesc($key, true);
            }
            $reply[] = '';
            $reply[] = '【工具命令】';
            $utilCommands = ['获取ID', '获取群ID'];
            foreach ($utilCommands as $key) {
                $reply[] = '/' . $key;
                $reply[] = self::getCommandDesc($key, true);
            }
        } else {
            $reply[] = '***** Snake Chain Game - Command List *****';
            $reply[] = '';
            $reply[] = '【Player Commands】';
            $playerCommands = ['help', 'start', 'rules', 'snake', 'bind_wallet', 'unbind_wallet', 'my_wallet', 'my_tickets', 'my_wins', 'prize_pool', 'recent_wins', 'stats'];
            foreach ($playerCommands as $key) {
                $reply[] = '/' . $key;
                $reply[] = self::getCommandDesc($key, false);
            }
            $reply[] = '';
            $reply[] = '【Admin Commands】';
            $adminCommands = ['wallet_change', 'cancel_wallet_change', 'group_config'];
            foreach ($adminCommands as $key) {
                $reply[] = '/' . $key;
                $reply[] = self::getCommandDesc($key, false);
            }
            $reply[] = '';
            $reply[] = '【Utility Commands】';
            $utilCommands = ['get_id', 'get_group_id'];
            foreach ($utilCommands as $key) {
                $reply[] = '/' . $key;
                $reply[] = self::getCommandDesc($key, false);
            }
        }
        return $reply;
    }

    /**
     * 获取命令描述（支持大小写不敏感）
     */
    private static function getCommandDesc(string $command, bool $isCn): string
    {
        $descMap = $isCn ? self::$commandDescCnMap : self::$commandDescMap;

        // 如果直接存在，直接返回
        if (isset($descMap[$command])) {
            return $descMap[$command];
        }

        // 大小写不敏感查找
        $commandLower = $isCn ? mb_strtolower($command, 'UTF-8') : strtolower($command);
        foreach ($descMap as $key => $value) {
            $keyLower = $isCn ? mb_strtolower($key, 'UTF-8') : strtolower($key);
            if ($keyLower === $commandLower) {
                return $value;
            }
        }

        return '';
    }
}
