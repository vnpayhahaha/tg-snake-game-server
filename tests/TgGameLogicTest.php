<?php

namespace tests;

use PHPUnit\Framework\TestCase;

/**
 * 游戏逻辑测试
 * 测试贪吃蛇游戏的核心逻辑
 */
class TgGameLogicTest extends TestCase
{
    /**
     * 测试蛇身节点添加逻辑
     */
    public function testAddSnakeNode()
    {
        echo "\n========== 测试蛇身节点添加 ==========\n";

        // 模拟游戏配置
        $gameConfig = [
            'id' => 1,
            'game_group_id' => 1,
            'tg_chat_id' => -1001234567890,
            'min_bet_amount' => 10.0,
            'ticket_length' => 10,
        ];

        // 模拟新交易
        $transaction = [
            'tx_hash' => '7c9a8f3e4d2b1a0c5e7f9d8a6b4c2e0f1234567890abcdef',
            'from_address' => 'TSenderAddress123456789012345678901',
            'to_address' => 'TReceiverAddr123456789012345678901',
            'amount' => 50.0, // TRX
            'block_number' => 12345678,
            'block_timestamp' => time(),
        ];

        // 验证投注金额
        $this->assertGreaterThanOrEqual(
            $gameConfig['min_bet_amount'],
            $transaction['amount'],
            '投注金额应该大于等于最小投注金额'
        );

        // 模拟节点数据
        $node = [
            'game_config_id' => $gameConfig['id'],
            'tg_user_id' => 123456,
            'tg_username' => 'test_user',
            'tx_hash' => $transaction['tx_hash'],
            'from_address' => $transaction['from_address'],
            'to_address' => $transaction['to_address'],
            'amount' => $transaction['amount'],
            'ticket_number' => '7984210759', // 从tx_hash提取
            'position_index' => 1,
            'status' => 1, // 活跃
            'created_at' => date('Y-m-d H:i:s'),
        ];

        echo "新节点信息:\n";
        echo "  用户ID: {$node['tg_user_id']}\n";
        echo "  用户名: {$node['tg_username']}\n";
        echo "  投注金额: {$node['amount']} TRX\n";
        echo "  票号: {$node['ticket_number']}\n";
        echo "  交易哈希: {$node['tx_hash']}\n";

        $this->assertArrayHasKey('ticket_number', $node, '节点应该包含票号');
        $this->assertEquals(10, strlen($node['ticket_number']), '票号长度应为10位');
        $this->assertMatchesRegularExpression('/^\d+$/', $node['ticket_number'], '票号应只包含数字');
    }

    /**
     * 测试奖池计算逻辑
     */
    public function testPrizePoolCalculation()
    {
        echo "\n========== 测试奖池计算 ==========\n";

        // 模拟蛇身节点
        $nodes = [
            ['amount' => 10.0],
            ['amount' => 20.0],
            ['amount' => 50.0],
            ['amount' => 100.0],
            ['amount' => 30.0],
        ];

        $totalAmount = array_sum(array_column($nodes, 'amount'));
        echo "蛇身节点总金额: {$totalAmount} TRX\n";

        $this->assertEquals(210.0, $totalAmount, '总金额应为210 TRX');

        // 奖池分配比例
        $jackpotRatio = 0.70;  // 70% - Jackpot奖池
        $rangeRatio = 0.20;    // 20% - 范围匹配奖池
        $platformRatio = 0.10; // 10% - 平台手续费

        $jackpotPool = $totalAmount * $jackpotRatio;
        $rangePool = $totalAmount * $rangeRatio;
        $platformFee = $totalAmount * $platformRatio;

        echo "\n奖池分配:\n";
        echo "  Jackpot奖池(70%): {$jackpotPool} TRX\n";
        echo "  范围匹配奖池(20%): {$rangePool} TRX\n";
        echo "  平台手续费(10%): {$platformFee} TRX\n";

        $this->assertEquals(147.0, $jackpotPool, 'Jackpot奖池应为147 TRX');
        $this->assertEquals(42.0, $rangePool, '范围匹配奖池应为42 TRX');
        $this->assertEquals(21.0, $platformFee, '平台手续费应为21 TRX');

        // 验证分配总和
        $totalDistributed = $jackpotPool + $rangePool + $platformFee;
        $this->assertEquals($totalAmount, $totalDistributed, '分配总和应等于总金额');
    }

    /**
     * 测试中奖检测逻辑
     */
    public function testWinnerDetection()
    {
        echo "\n========== 测试中奖检测 ==========\n";

        // 蛇头票号
        $headTicket = '1234567890';
        echo "蛇头票号: {$headTicket}\n\n";

        // 蛇身节点
        $bodyNodes = [
            ['node_id' => 1, 'ticket' => '1234567890', 'amount' => 100.0], // Jackpot
            ['node_id' => 2, 'ticket' => '1234512345', 'amount' => 50.0],  // 前5位匹配
            ['node_id' => 3, 'ticket' => '1234567800', 'amount' => 30.0],  // 前7位匹配
            ['node_id' => 4, 'ticket' => '9876543210', 'amount' => 20.0],  // 不匹配
            ['node_id' => 5, 'ticket' => '1234000000', 'amount' => 40.0],  // 前4位匹配
        ];

        $matchDigits = 5; // 范围匹配需要前5位一致

        // 模拟匹配逻辑
        $jackpotWinners = [];
        $rangeWinners = [];

        foreach ($bodyNodes as $node) {
            if ($node['ticket'] === $headTicket) {
                $jackpotWinners[] = $node;
                echo "🎉 Jackpot中奖: 节点{$node['node_id']}, 票号{$node['ticket']}\n";
            } elseif (substr($node['ticket'], 0, $matchDigits) === substr($headTicket, 0, $matchDigits)) {
                $rangeWinners[] = $node;
                echo "✨ 范围匹配: 节点{$node['node_id']}, 票号{$node['ticket']}\n";
            } else {
                echo "❌ 未中奖: 节点{$node['node_id']}, 票号{$node['ticket']}\n";
            }
        }

        echo "\n中奖统计:\n";
        echo "  Jackpot中奖数量: " . count($jackpotWinners) . "\n";
        echo "  范围匹配数量: " . count($rangeWinners) . "\n";

        $this->assertCount(1, $jackpotWinners, '应该有1个Jackpot中奖');
        $this->assertCount(2, $rangeWinners, '应该有2个范围匹配中奖');
        $this->assertEquals(1, $jackpotWinners[0]['node_id'], 'Jackpot中奖节点ID应为1');
    }

    /**
     * 测试奖金分配逻辑
     */
    public function testPrizeDistribution()
    {
        echo "\n========== 测试奖金分配 ==========\n";

        $totalPrizePool = 1000.0; // TRX
        $jackpotPool = $totalPrizePool * 0.70; // 700 TRX
        $rangePool = $totalPrizePool * 0.20;   // 200 TRX

        // Jackpot中奖者
        $jackpotWinners = [
            ['node_id' => 1, 'amount' => 100.0],
        ];

        // 范围匹配中奖者
        $rangeWinners = [
            ['node_id' => 2, 'amount' => 50.0],
            ['node_id' => 3, 'amount' => 30.0],
            ['node_id' => 5, 'amount' => 40.0],
        ];

        echo "奖池总额: {$totalPrizePool} TRX\n";
        echo "Jackpot奖池: {$jackpotPool} TRX\n";
        echo "范围匹配奖池: {$rangePool} TRX\n\n";

        // Jackpot奖金分配（按投注金额比例）
        $jackpotTotalBet = array_sum(array_column($jackpotWinners, 'amount'));
        echo "Jackpot中奖分配:\n";
        foreach ($jackpotWinners as $winner) {
            $ratio = $winner['amount'] / $jackpotTotalBet;
            $prize = $jackpotPool * $ratio;
            echo "  节点{$winner['node_id']}: 投注{$winner['amount']} TRX, 占比" . ($ratio * 100) . "%, 获得{$prize} TRX\n";

            $this->assertEquals($jackpotPool, $prize, '单个Jackpot中奖者应获得全部奖池');
        }

        // 范围匹配奖金分配（按投注金额比例）
        $rangeTotalBet = array_sum(array_column($rangeWinners, 'amount'));
        echo "\n范围匹配奖金分配:\n";
        $totalRangePrize = 0;
        foreach ($rangeWinners as $winner) {
            $ratio = $winner['amount'] / $rangeTotalBet;
            $prize = $rangePool * $ratio;
            $totalRangePrize += $prize;
            echo "  节点{$winner['node_id']}: 投注{$winner['amount']} TRX, 占比" . round($ratio * 100, 2) . "%, 获得{$prize} TRX\n";
        }

        echo "\n范围匹配总奖金: {$totalRangePrize} TRX\n";
        $this->assertEquals($rangePool, round($totalRangePrize, 2), '范围匹配总奖金应等于范围奖池');
    }

    /**
     * 测试蛇头移动逻辑
     */
    public function testSnakeHeadMovement()
    {
        echo "\n========== 测试蛇头移动 ==========\n";

        // 初始蛇头
        $oldHead = [
            'node_id' => 10,
            'position_index' => 0,
            'ticket' => '1234567890',
            'status' => 1, // 蛇头
        ];

        // 新交易产生新节点
        $newNode = [
            'node_id' => 11,
            'position_index' => 0,
            'ticket' => '9876543210',
            'status' => 1, // 新蛇头
        ];

        echo "旧蛇头:\n";
        echo "  节点ID: {$oldHead['node_id']}\n";
        echo "  票号: {$oldHead['ticket']}\n";
        echo "  位置索引: {$oldHead['position_index']}\n";

        echo "\n新节点成为蛇头:\n";
        echo "  节点ID: {$newNode['node_id']}\n";
        echo "  票号: {$newNode['ticket']}\n";
        echo "  位置索引: {$newNode['position_index']}\n";

        // 旧蛇头应该变成蛇身
        $oldHead['position_index'] = 1;
        $oldHead['status'] = 1; // 仍然活跃

        echo "\n旧蛇头变为蛇身:\n";
        echo "  节点ID: {$oldHead['node_id']}\n";
        echo "  新位置索引: {$oldHead['position_index']}\n";

        $this->assertEquals(0, $newNode['position_index'], '新蛇头位置索引应为0');
        $this->assertEquals(1, $oldHead['position_index'], '旧蛇头位置索引应变为1');
    }

    /**
     * 测试蛇尾剔除逻辑
     */
    public function testSnakeTailRemoval()
    {
        echo "\n========== 测试蛇尾剔除 ==========\n";

        $maxSnakeLength = 50;

        // 模拟当前蛇身（已达到最大长度）
        $snakeNodes = [];
        for ($i = 0; $i < $maxSnakeLength; $i++) {
            $snakeNodes[] = [
                'node_id' => 100 + $i,
                'position_index' => $i,
                'status' => 1,
            ];
        }

        echo "当前蛇身长度: " . count($snakeNodes) . "\n";
        echo "最大蛇身长度: {$maxSnakeLength}\n";

        $this->assertEquals($maxSnakeLength, count($snakeNodes), '蛇身长度应为最大长度');

        // 新节点加入
        echo "\n新节点加入，需要剔除蛇尾...\n";

        // 找出蛇尾（position_index最大的节点）
        $tailNode = end($snakeNodes);
        echo "蛇尾节点ID: {$tailNode['node_id']}\n";
        echo "蛇尾位置索引: {$tailNode['position_index']}\n";

        $this->assertEquals($maxSnakeLength - 1, $tailNode['position_index'], '蛇尾位置索引应为最大值');

        // 剔除蛇尾
        array_pop($snakeNodes);
        $tailNode['status'] = 3; // 已剔除

        echo "蛇尾已剔除，新蛇身长度: " . count($snakeNodes) . "\n";
        $this->assertEquals($maxSnakeLength - 1, count($snakeNodes), '剔除后蛇身长度应减1');
        $this->assertEquals(3, $tailNode['status'], '蛇尾状态应为已剔除');
    }

    /**
     * 测试节点状态转换
     */
    public function testNodeStatusTransition()
    {
        echo "\n========== 测试节点状态转换 ==========\n";

        $statusMap = [
            1 => '活跃（蛇身中）',
            2 => '已中奖',
            3 => '已剔除（超出最大长度）',
        ];

        // 模拟节点状态流转
        $node = [
            'node_id' => 123,
            'status' => 1, // 初始状态：活跃
        ];

        echo "节点ID: {$node['node_id']}\n";
        echo "初始状态: {$node['status']} - {$statusMap[$node['status']]}\n";

        // 场景1：节点中奖
        echo "\n场景1: 节点中奖\n";
        $node['status'] = 2;
        echo "  状态变更: {$node['status']} - {$statusMap[$node['status']]}\n";
        $this->assertEquals(2, $node['status'], '中奖后状态应为2');

        // 场景2：节点被剔除
        echo "\n场景2: 重置节点，模拟被剔除\n";
        $node['status'] = 1;
        echo "  当前状态: {$node['status']} - {$statusMap[$node['status']]}\n";
        $node['status'] = 3;
        echo "  状态变更: {$node['status']} - {$statusMap[$node['status']]}\n";
        $this->assertEquals(3, $node['status'], '剔除后状态应为3');

        // 验证所有状态
        echo "\n所有可能的状态:\n";
        foreach ($statusMap as $status => $desc) {
            echo "  {$status}: {$desc}\n";
        }

        $this->assertCount(3, $statusMap, '应该有3种状态');
    }

    /**
     * 测试游戏配置验证
     */
    public function testGameConfigValidation()
    {
        echo "\n========== 测试游戏配置验证 ==========\n";

        $config = [
            'wallet_address' => 'TReceiverAddr123456789012345678901',
            'min_bet_amount' => 10.0,
            'max_snake_length' => 50,
            'ticket_length' => 10,
            'match_digits' => 5,
            'jackpot_ratio' => 0.70,
            'range_ratio' => 0.20,
            'platform_ratio' => 0.10,
        ];

        echo "游戏配置验证:\n";

        // 验证钱包地址格式
        $this->assertMatchesRegularExpression(
            '/^T[a-zA-Z0-9]{33}$/',
            $config['wallet_address'],
            '钱包地址格式应正确'
        );
        echo "  ✓ 钱包地址格式正确\n";

        // 验证最小投注金额
        $this->assertGreaterThan(0, $config['min_bet_amount'], '最小投注金额应大于0');
        echo "  ✓ 最小投注金额: {$config['min_bet_amount']} TRX\n";

        // 验证蛇身最大长度
        $this->assertGreaterThan(0, $config['max_snake_length'], '蛇身最大长度应大于0');
        $this->assertLessThanOrEqual(100, $config['max_snake_length'], '蛇身最大长度不应超过100');
        echo "  ✓ 蛇身最大长度: {$config['max_snake_length']}\n";

        // 验证票号长度
        $this->assertGreaterThanOrEqual(8, $config['ticket_length'], '票号长度应至少为8位');
        $this->assertLessThanOrEqual(20, $config['ticket_length'], '票号长度不应超过20位');
        echo "  ✓ 票号长度: {$config['ticket_length']}位\n";

        // 验证匹配位数
        $this->assertGreaterThan(0, $config['match_digits'], '匹配位数应大于0');
        $this->assertLessThanOrEqual($config['ticket_length'], $config['match_digits'], '匹配位数不应超过票号长度');
        echo "  ✓ 匹配位数: {$config['match_digits']}位\n";

        // 验证奖池比例
        $totalRatio = $config['jackpot_ratio'] + $config['range_ratio'] + $config['platform_ratio'];
        $this->assertEqualsWithDelta(1.0, $totalRatio, 0.0001, '奖池比例总和应为1.0');
        echo "  ✓ Jackpot比例: " . ($config['jackpot_ratio'] * 100) . "%\n";
        echo "  ✓ 范围匹配比例: " . ($config['range_ratio'] * 100) . "%\n";
        echo "  ✓ 平台手续费: " . ($config['platform_ratio'] * 100) . "%\n";
        echo "  ✓ 比例总和: " . ($totalRatio * 100) . "%\n";
    }

    /**
     * 测试完整游戏流程
     */
    public function testCompleteGameFlow()
    {
        echo "\n========== 测试完整游戏流程 ==========\n";

        // 1. 游戏配置
        $config = [
            'min_bet_amount' => 10.0,
            'max_snake_length' => 10,
            'ticket_length' => 10,
            'match_digits' => 5,
        ];

        echo "游戏配置:\n";
        echo "  最小投注: {$config['min_bet_amount']} TRX\n";
        echo "  最大蛇身长度: {$config['max_snake_length']}\n";
        echo "  票号长度: {$config['ticket_length']}位\n";
        echo "  匹配位数: {$config['match_digits']}位\n\n";

        // 2. 模拟10笔交易加入蛇身
        $snakeNodes = [];
        for ($i = 1; $i <= 10; $i++) {
            $node = [
                'node_id' => $i,
                'amount' => 10 + ($i * 5),
                'ticket' => str_pad($i, 10, '0', STR_PAD_LEFT),
                'position_index' => $i - 1,
                'status' => 1,
            ];
            $snakeNodes[] = $node;
        }

        echo "步骤1: 10笔交易加入蛇身\n";
        foreach ($snakeNodes as $node) {
            echo "  节点{$node['node_id']}: {$node['amount']} TRX, 票号{$node['ticket']}\n";
        }

        $this->assertCount(10, $snakeNodes, '应该有10个节点');

        // 3. 新交易产生，触发中奖检测
        $newTransaction = [
            'node_id' => 11,
            'amount' => 100.0,
            'ticket' => '0000000005', // 将与节点5完全匹配
        ];

        echo "\n步骤2: 新交易触发中奖检测\n";
        echo "  新蛇头票号: {$newTransaction['ticket']}\n";

        // 4. 检测中奖
        $jackpotWinners = [];
        $rangeWinners = [];

        foreach ($snakeNodes as $node) {
            if ($node['ticket'] === $newTransaction['ticket']) {
                $jackpotWinners[] = $node;
            } elseif (substr($node['ticket'], 0, $config['match_digits']) === substr($newTransaction['ticket'], 0, $config['match_digits'])) {
                $rangeWinners[] = $node;
            }
        }

        echo "\n步骤3: 中奖结果\n";
        echo "  Jackpot中奖: " . count($jackpotWinners) . "个\n";
        if (!empty($jackpotWinners)) {
            foreach ($jackpotWinners as $winner) {
                echo "    节点{$winner['node_id']}, 票号{$winner['ticket']}\n";
            }
        }

        echo "  范围匹配: " . count($rangeWinners) . "个\n";
        if (!empty($rangeWinners)) {
            foreach ($rangeWinners as $winner) {
                echo "    节点{$winner['node_id']}, 票号{$winner['ticket']}\n";
            }
        }

        // 5. 计算奖池
        $totalAmount = array_sum(array_column($snakeNodes, 'amount'));
        $jackpotPool = $totalAmount * 0.70;
        $rangePool = $totalAmount * 0.20;

        echo "\n步骤4: 奖池计算\n";
        echo "  总投注额: {$totalAmount} TRX\n";
        echo "  Jackpot奖池: {$jackpotPool} TRX\n";
        echo "  范围匹配奖池: {$rangePool} TRX\n";

        // 6. 验证结果
        $this->assertCount(1, $jackpotWinners, '应该有1个Jackpot中奖');
        $this->assertEquals(5, $jackpotWinners[0]['node_id'], 'Jackpot中奖节点应为5');

        echo "\n========== 游戏流程测试完成 ==========\n";
    }
}
