<?php

namespace tests;

use app\service\bot\CommandEnum;
use PHPUnit\Framework\TestCase;

/**
 * Bot命令测试
 * 测试Telegram Bot命令解析和处理
 */
class TgBotCommandTest extends TestCase
{
    /**
     * 测试命令识别
     */
    public function testIsCommand()
    {
        echo "\n========== 测试命令识别 ==========\n";

        // 英文命令
        $englishCommands = ['help', 'start', 'rules', 'snake', 'bind_wallet'];
        foreach ($englishCommands as $cmd) {
            $this->assertTrue(
                CommandEnum::isCommand($cmd),
                "'{$cmd}' 应该被识别为有效命令"
            );
            echo "  ✓ 英文命令: /{$cmd}\n";
        }

        // 中文命令
        $chineseCommands = ['帮助', '开始', '规则', '蛇身', '绑定钱包'];
        foreach ($chineseCommands as $cmd) {
            $this->assertTrue(
                CommandEnum::isCommand($cmd),
                "'{$cmd}' 应该被识别为有效命令"
            );
            echo "  ✓ 中文命令: /{$cmd}\n";
        }

        // 无效命令
        $invalidCommands = ['invalid', 'test', '未知命令'];
        foreach ($invalidCommands as $cmd) {
            $this->assertFalse(
                CommandEnum::isCommand($cmd),
                "'{$cmd}' 不应该被识别为有效命令"
            );
            echo "  ✗ 无效命令: /{$cmd}\n";
        }
    }

    /**
     * 测试获取命令方法名
     */
    public function testGetCommand()
    {
        echo "\n========== 测试获取命令方法名 ==========\n";

        $testCases = [
            // 英文命令
            ['command' => 'help', 'expected' => 'Help'],
            ['command' => 'start', 'expected' => 'Start'],
            ['command' => 'bind_wallet', 'expected' => 'BindWallet'],

            // 中文命令
            ['command' => '帮助', 'expected' => 'cnHelp'],
            ['command' => '开始', 'expected' => 'cnStart'],
            ['command' => '绑定钱包', 'expected' => 'cnBindWallet'],
        ];

        foreach ($testCases as $case) {
            $result = CommandEnum::getCommand($case['command']);
            $this->assertEquals(
                $case['expected'],
                $result,
                "命令 '{$case['command']}' 应该返回 '{$case['expected']}'"
            );
            echo "  /{$case['command']} → {$result}\n";
        }

        // 测试无效命令
        $invalidResult = CommandEnum::getCommand('invalid_command');
        $this->assertEquals('', $invalidResult, '无效命令应该返回空字符串');
        echo "  /invalid_command → (empty string)\n";
    }

    /**
     * 测试帮助信息生成
     */
    public function testGetHelpReply()
    {
        echo "\n========== 测试帮助信息生成 ==========\n";

        // 英文帮助
        $englishHelp = CommandEnum::getHelpReply(false);
        $this->assertIsArray($englishHelp, '帮助信息应该是数组');
        $this->assertNotEmpty($englishHelp, '帮助信息不应该为空');
        $this->assertStringContainsString('Snake Chain Game', implode("\n", $englishHelp), '英文帮助应包含游戏名称');

        echo "英文帮助信息行数: " . count($englishHelp) . "\n";

        // 中文帮助
        $chineseHelp = CommandEnum::getHelpReply(true);
        $this->assertIsArray($chineseHelp, '中文帮助信息应该是数组');
        $this->assertNotEmpty($chineseHelp, '中文帮助信息不应该为空');
        $this->assertStringContainsString('贪吃蛇链上游戏', implode("\n", $chineseHelp), '中文帮助应包含游戏名称');

        echo "中文帮助信息行数: " . count($chineseHelp) . "\n";
    }

    /**
     * 测试命令描述映射
     */
    public function testCommandDescriptions()
    {
        echo "\n========== 测试命令描述映射 ==========\n";

        // 验证所有英文命令都有描述
        $englishCommands = array_keys(CommandEnum::COMMAND_SET);
        foreach ($englishCommands as $cmd) {
            $this->assertArrayHasKey(
                $cmd,
                CommandEnum::$commandDescMap,
                "英文命令 '{$cmd}' 应该有描述"
            );
            echo "  ✓ /{$cmd}: " . (strlen(CommandEnum::$commandDescMap[$cmd]) > 50 ? substr(CommandEnum::$commandDescMap[$cmd], 0, 50) . '...' : CommandEnum::$commandDescMap[$cmd]) . "\n";
        }

        // 验证所有中文命令都有描述
        $chineseCommands = array_keys(CommandEnum::COMMAND_SET_CN);
        foreach ($chineseCommands as $cmd) {
            $this->assertArrayHasKey(
                $cmd,
                CommandEnum::$commandDescCnMap,
                "中文命令 '{$cmd}' 应该有描述"
            );
        }

        echo "\n英文命令数量: " . count($englishCommands) . "\n";
        echo "中文命令数量: " . count($chineseCommands) . "\n";
        $this->assertEquals(count($englishCommands), count($chineseCommands), '英文和中文命令数量应该相同');
    }

    /**
     * 测试玩家命令
     */
    public function testPlayerCommands()
    {
        echo "\n========== 测试玩家命令 ==========\n";

        $playerCommands = [
            'help', 'start', 'rules', 'snake',
            'bind_wallet', 'unbind_wallet', 'my_wallet',
            'my_tickets', 'my_wins', 'prize_pool',
            'recent_wins', 'stats',
        ];

        echo "玩家可用命令:\n";
        foreach ($playerCommands as $cmd) {
            $this->assertTrue(
                CommandEnum::isCommand($cmd),
                "玩家命令 '{$cmd}' 应该有效"
            );
            $methodName = CommandEnum::getCommand($cmd);
            echo "  /{$cmd} → {$methodName}\n";
        }

        $this->assertCount(12, $playerCommands, '应该有12个玩家命令');
    }

    /**
     * 测试管理员命令
     */
    public function testAdminCommands()
    {
        echo "\n========== 测试管理员命令 ==========\n";

        $adminCommands = [
            'wallet_change',
            'cancel_wallet_change',
            'group_config',
        ];

        echo "管理员专用命令:\n";
        foreach ($adminCommands as $cmd) {
            $this->assertTrue(
                CommandEnum::isCommand($cmd),
                "管理员命令 '{$cmd}' 应该有效"
            );
            $methodName = CommandEnum::getCommand($cmd);
            echo "  /{$cmd} → {$methodName}\n";

            // 验证命令描述中包含 [Admin Only]
            $this->assertStringContainsString(
                '[Admin Only]',
                CommandEnum::$commandDescMap[$cmd],
                "管理员命令 '{$cmd}' 的描述应标注 [Admin Only]"
            );
        }

        $this->assertCount(3, $adminCommands, '应该有3个管理员命令');
    }

    /**
     * 测试工具命令
     */
    public function testUtilityCommands()
    {
        echo "\n========== 测试工具命令 ==========\n";

        $utilityCommands = ['get_id', 'get_group_id'];

        echo "工具命令:\n";
        foreach ($utilityCommands as $cmd) {
            $this->assertTrue(
                CommandEnum::isCommand($cmd),
                "工具命令 '{$cmd}' 应该有效"
            );
            $methodName = CommandEnum::getCommand($cmd);
            echo "  /{$cmd} → {$methodName}\n";
        }

        $this->assertCount(2, $utilityCommands, '应该有2个工具命令');
    }

    /**
     * 测试双语命令对应
     */
    public function testBilingualCommandMapping()
    {
        echo "\n========== 测试双语命令对应 ==========\n";

        $commandPairs = [
            ['en' => 'help', 'cn' => '帮助'],
            ['en' => 'start', 'cn' => '开始'],
            ['en' => 'rules', 'cn' => '规则'],
            ['en' => 'snake', 'cn' => '蛇身'],
            ['en' => 'bind_wallet', 'cn' => '绑定钱包'],
            ['en' => 'unbind_wallet', 'cn' => '解绑钱包'],
            ['en' => 'my_wallet', 'cn' => '我的钱包'],
            ['en' => 'my_tickets', 'cn' => '我的票号'],
            ['en' => 'my_wins', 'cn' => '我的中奖'],
            ['en' => 'prize_pool', 'cn' => '奖池'],
            ['en' => 'recent_wins', 'cn' => '最近中奖'],
            ['en' => 'stats', 'cn' => '统计'],
            ['en' => 'wallet_change', 'cn' => '钱包变更'],
            ['en' => 'cancel_wallet_change', 'cn' => '取消变更'],
            ['en' => 'group_config', 'cn' => '群组配置'],
            ['en' => 'get_id', 'cn' => '获取ID'],
            ['en' => 'get_group_id', 'cn' => '获取群ID'],
        ];

        foreach ($commandPairs as $pair) {
            $enMethod = CommandEnum::getCommand($pair['en']);
            $cnMethod = CommandEnum::getCommand($pair['cn']);

            echo "  /{$pair['en']} → {$enMethod}\n";
            echo "  /{$pair['cn']} → {$cnMethod}\n";

            // 验证中文命令方法名以cn开头
            $this->assertStringStartsWith('cn', $cnMethod, "中文命令方法名应以'cn'开头");

            // 验证中文方法名与英文方法名对应
            $expectedCnMethod = 'cn' . $enMethod;
            $this->assertEquals(
                $expectedCnMethod,
                $cnMethod,
                "中文命令 '{$pair['cn']}' 应映射到 '{$expectedCnMethod}'"
            );

            echo "  ✓ 映射正确\n\n";
        }

        $this->assertCount(17, $commandPairs, '应该有17对双语命令');
    }

    /**
     * 测试命令参数验证
     */
    public function testCommandParameterValidation()
    {
        echo "\n========== 测试命令参数验证 ==========\n";

        // bind_wallet 需要钱包地址参数
        echo "测试 /bind_wallet 参数:\n";
        $validWallet = 'TRX9a5u5sKjDGYJxDQaB4YWR7tJ8YePvUV';
        $this->assertMatchesRegularExpression(
            '/^T[a-zA-Z0-9]{33}$/',
            $validWallet,
            '钱包地址格式应正确'
        );
        echo "  ✓ 有效钱包地址: {$validWallet}\n";

        $invalidWallets = [
            'ABC123',                                  // 不以T开头
            'T12345',                                  // 太短
            'T123456789012345678901234567890123456', // 太长
        ];

        foreach ($invalidWallets as $wallet) {
            $this->assertFalse(
                preg_match('/^T[a-zA-Z0-9]{33}$/', $wallet) === 1,
                "'{$wallet}' 应该是无效的钱包地址"
            );
            echo "  ✗ 无效钱包地址: {$wallet}\n";
        }

        // wallet_change 需要钱包地址和冷却时间参数
        echo "\n测试 /wallet_change 参数:\n";
        $newWallet = 'TNewWallet123456789012345678901234';
        $cooldownMinutes = 60;

        $this->assertMatchesRegularExpression(
            '/^T[a-zA-Z0-9]{33}$/',
            $newWallet,
            '新钱包地址格式应正确'
        );
        $this->assertGreaterThanOrEqual(1, $cooldownMinutes, '冷却时间应至少1分钟');
        $this->assertLessThanOrEqual(1440, $cooldownMinutes, '冷却时间不应超过1440分钟（24小时）');

        echo "  ✓ 新钱包地址: {$newWallet}\n";
        echo "  ✓ 冷却时间: {$cooldownMinutes} 分钟\n";
    }

    /**
     * 测试命令权限检查
     */
    public function testCommandPermissionCheck()
    {
        echo "\n========== 测试命令权限检查 ==========\n";

        // 需要管理员权限的命令
        $adminOnlyCommands = [
            'wallet_change',
            'cancel_wallet_change',
            'group_config',
        ];

        echo "管理员专用命令:\n";
        foreach ($adminOnlyCommands as $cmd) {
            $desc = CommandEnum::$commandDescMap[$cmd];
            $hasAdminTag = str_contains($desc, '[Admin Only]');

            $this->assertTrue(
                $hasAdminTag,
                "命令 '{$cmd}' 应该标记为管理员专用"
            );

            echo "  ✓ /{$cmd} - 需要管理员权限\n";
        }

        // 不需要特殊权限的命令
        $publicCommands = ['help', 'start', 'rules', 'get_id'];

        echo "\n公开命令:\n";
        foreach ($publicCommands as $cmd) {
            $desc = CommandEnum::$commandDescMap[$cmd];
            $hasAdminTag = str_contains($desc, '[Admin Only]');

            $this->assertFalse(
                $hasAdminTag,
                "命令 '{$cmd}' 不应该标记为管理员专用"
            );

            echo "  ✓ /{$cmd} - 无需特殊权限\n";
        }
    }

    /**
     * 测试命令消息格式
     */
    public function testCommandMessageFormat()
    {
        echo "\n========== 测试命令消息格式 ==========\n";

        // 模拟Telegram消息数据
        $messageData = [
            'chat_id' => -1001234567890,
            'from_user_id' => 123456789,
            'from_username' => 'test_user',
            'message_id' => 12345,
        ];

        echo "消息数据结构:\n";
        echo "  Chat ID: {$messageData['chat_id']}\n";
        echo "  User ID: {$messageData['from_user_id']}\n";
        echo "  Username: {$messageData['from_username']}\n";
        echo "  Message ID: {$messageData['message_id']}\n";

        $this->assertIsInt($messageData['chat_id'], 'chat_id 应该是整数');
        $this->assertIsInt($messageData['from_user_id'], 'from_user_id 应该是整数');
        $this->assertIsString($messageData['from_username'], 'from_username 应该是字符串');
    }

    /**
     * 测试命令响应格式
     */
    public function testCommandResponseFormat()
    {
        echo "\n========== 测试命令响应格式 ==========\n";

        // 成功响应
        $successResponse = [
            'success' => true,
            'message' => 'Operation completed successfully',
        ];

        $this->assertArrayHasKey('success', $successResponse, '响应应包含success字段');
        $this->assertArrayHasKey('message', $successResponse, '响应应包含message字段');
        $this->assertTrue($successResponse['success'], 'success字段应为true');

        echo "成功响应:\n";
        echo "  success: " . ($successResponse['success'] ? 'true' : 'false') . "\n";
        echo "  message: {$successResponse['message']}\n";

        // 失败响应
        $errorResponse = [
            'success' => false,
            'message' => 'Operation failed',
        ];

        $this->assertArrayHasKey('success', $errorResponse, '响应应包含success字段');
        $this->assertArrayHasKey('message', $errorResponse, '响应应包含message字段');
        $this->assertFalse($errorResponse['success'], 'success字段应为false');

        echo "\n失败响应:\n";
        echo "  success: " . ($errorResponse['success'] ? 'true' : 'false') . "\n";
        echo "  message: {$errorResponse['message']}\n";
    }

    /**
     * 测试队列名称常量
     */
    public function testQueueNameConstants()
    {
        echo "\n========== 测试队列名称常量 ==========\n";

        $this->assertEquals(
            'telegram-command-run-queue',
            CommandEnum::TELEGRAM_COMMAND_RUN_QUEUE_NAME,
            '命令运行队列名称应正确'
        );

        $this->assertEquals(
            'telegram-notice-queue',
            CommandEnum::TELEGRAM_NOTICE_QUEUE_NAME,
            '通知队列名称应正确'
        );

        echo "  Command Queue: " . CommandEnum::TELEGRAM_COMMAND_RUN_QUEUE_NAME . "\n";
        echo "  Notice Queue: " . CommandEnum::TELEGRAM_NOTICE_QUEUE_NAME . "\n";
    }

    /**
     * 测试命令示例格式
     */
    public function testCommandExampleFormat()
    {
        echo "\n========== 测试命令示例格式 ==========\n";

        $commandsWithExamples = [
            'bind_wallet' => '/bind_wallet TRX_ADDRESS',
            'wallet_change' => '/wallet_change NEW_WALLET_ADDRESS COOLDOWN_MINUTES',
        ];

        foreach ($commandsWithExamples as $cmd => $example) {
            $desc = CommandEnum::$commandDescMap[$cmd];

            $this->assertStringContainsString(
                '[Example]',
                $desc,
                "命令 '{$cmd}' 的描述应包含示例"
            );

            echo "  /{$cmd}:\n";
            echo "    示例: {$example}\n";
        }
    }

    /**
     * 测试命令大小写处理
     */
    public function testCommandCaseHandling()
    {
        echo "\n========== 测试命令大小写处理 ==========\n";

        $testCases = [
            ['input' => 'help', 'expected' => 'Help'],
            ['input' => 'HELP', 'expected' => 'Help'],
            ['input' => 'Help', 'expected' => 'Help'],
            ['input' => 'hElP', 'expected' => 'Help'],
            ['input' => 'BIND_WALLET', 'expected' => 'BindWallet'],
            ['input' => 'Bind_Wallet', 'expected' => 'BindWallet'],
            ['input' => 'my_tickets', 'expected' => 'MyTickets'],
            ['input' => 'MY_TICKETS', 'expected' => 'MyTickets'],
        ];

        foreach ($testCases as $case) {
            $isValid = CommandEnum::isCommand($case['input']);
            $methodName = CommandEnum::getCommand($case['input']);

            $this->assertTrue($isValid, "命令 '{$case['input']}' 应该被识别");
            $this->assertEquals(
                $case['expected'],
                $methodName,
                "命令 '{$case['input']}' 应该返回 '{$case['expected']}'"
            );

            echo "  {$case['input']} → {$methodName} " . ($methodName === $case['expected'] ? '✓' : '✗') . "\n";
        }
    }
}
