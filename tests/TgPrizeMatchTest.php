<?php

namespace tests;

use app\lib\helper\TicketNumberHelper;
use PHPUnit\Framework\TestCase;

/**
 * 中奖匹配测试
 * 测试贪吃蛇游戏的中奖匹配算法
 */
class TgPrizeMatchTest extends TestCase
{
    /**
     * 测试Jackpot完全匹配
     */
    public function testJackpotPerfectMatch()
    {
        echo "\n========== 测试Jackpot完全匹配 ==========\n";

        $headTicket = '1234567890';
        $bodyTickets = [
            ['node_id' => 1, 'ticket' => '1234567890', 'amount' => 100.0], // 完全匹配
            ['node_id' => 2, 'ticket' => '1234567891', 'amount' => 50.0],  // 不匹配
            ['node_id' => 3, 'ticket' => '1234567890', 'amount' => 80.0],  // 完全匹配
            ['node_id' => 4, 'ticket' => '9876543210', 'amount' => 30.0],  // 不匹配
        ];

        echo "蛇头票号: {$headTicket}\n\n";

        $jackpotWinners = [];
        foreach ($bodyTickets as $node) {
            if (TicketNumberHelper::isJackpot($headTicket, $node['ticket'])) {
                $jackpotWinners[] = $node;
                echo "  🎉 Jackpot! 节点{$node['node_id']}: {$node['ticket']} (投注{$node['amount']} TRX)\n";
            }
        }

        $this->assertCount(2, $jackpotWinners, '应该有2个Jackpot中奖');
        $this->assertEquals(1, $jackpotWinners[0]['node_id']);
        $this->assertEquals(3, $jackpotWinners[1]['node_id']);

        echo "\nJackpot中奖总数: " . count($jackpotWinners) . "\n";
    }

    /**
     * 测试范围匹配（前N位匹配）
     */
    public function testRangeMatch()
    {
        echo "\n========== 测试范围匹配 ==========\n";

        $headTicket = '1234567890';
        $matchDigits = 5; // 前5位匹配

        $bodyTickets = [
            ['node_id' => 1, 'ticket' => '1234500000', 'amount' => 50.0],  // 前5位匹配
            ['node_id' => 2, 'ticket' => '1234512345', 'amount' => 60.0],  // 前5位匹配
            ['node_id' => 3, 'ticket' => '1234000000', 'amount' => 40.0],  // 前4位匹配（不够）
            ['node_id' => 4, 'ticket' => '1234567890', 'amount' => 100.0], // 完全匹配（不算范围匹配）
            ['node_id' => 5, 'ticket' => '9876543210', 'amount' => 30.0],  // 不匹配
        ];

        echo "蛇头票号: {$headTicket}\n";
        echo "匹配位数要求: 前{$matchDigits}位\n\n";

        $rangeWinners = [];
        foreach ($bodyTickets as $node) {
            $matchCount = TicketNumberHelper::getMatchDigits($headTicket, $node['ticket']);
            $isJackpot = TicketNumberHelper::isJackpot($headTicket, $node['ticket']);
            $isRangeMatch = TicketNumberHelper::isMatch($headTicket, $node['ticket'], $matchDigits) && !$isJackpot;

            echo "  节点{$node['node_id']}: {$node['ticket']} - 匹配{$matchCount}位";

            if ($isJackpot) {
                echo " (Jackpot)\n";
            } elseif ($isRangeMatch) {
                $rangeWinners[] = $node;
                echo " ✨ 范围匹配!\n";
            } else {
                echo " ✗\n";
            }
        }

        echo "\n范围匹配中奖总数: " . count($rangeWinners) . "\n";
        $this->assertCount(2, $rangeWinners, '应该有2个范围匹配中奖');
    }

    /**
     * 测试多个中奖者场景
     */
    public function testMultipleWinners()
    {
        echo "\n========== 测试多个中奖者 ==========\n";

        $headTicket = '5678901234';
        $matchDigits = 4;

        $bodyTickets = [
            ['node_id' => 1, 'ticket' => '5678901234', 'amount' => 100.0, 'user_id' => 1001], // Jackpot
            ['node_id' => 2, 'ticket' => '5678901234', 'amount' => 80.0, 'user_id' => 1002],  // Jackpot
            ['node_id' => 3, 'ticket' => '5678000000', 'amount' => 50.0, 'user_id' => 1003],  // 范围匹配
            ['node_id' => 4, 'ticket' => '5678123456', 'amount' => 60.0, 'user_id' => 1004],  // 范围匹配
            ['node_id' => 5, 'ticket' => '5678999999', 'amount' => 70.0, 'user_id' => 1005],  // 范围匹配
        ];

        echo "蛇头票号: {$headTicket}\n";
        echo "匹配位数: {$matchDigits}\n\n";

        $matches = TicketNumberHelper::findMatches($headTicket, $bodyTickets, $matchDigits);

        echo "Jackpot中奖:\n";
        foreach ($matches['jackpot'] as $winner) {
            echo "  节点{$winner['node_id']}, 用户{$winner['user_id']}, {$winner['amount']} TRX\n";
        }

        echo "\n范围匹配中奖:\n";
        foreach ($matches['range'] as $winner) {
            echo "  节点{$winner['node_id']}, 用户{$winner['user_id']}, {$winner['amount']} TRX\n";
        }

        $this->assertCount(2, $matches['jackpot'], '应该有2个Jackpot中奖');
        $this->assertCount(3, $matches['range'], '应该有3个范围匹配中奖');
    }

    /**
     * 测试奖金分配算法（按投注比例）
     */
    public function testPrizeDistributionByBetRatio()
    {
        echo "\n========== 测试奖金分配（按投注比例） ==========\n";

        $prizePool = 1000.0; // TRX

        $winners = [
            ['node_id' => 1, 'amount' => 100.0],
            ['node_id' => 2, 'amount' => 200.0],
            ['node_id' => 3, 'amount' => 300.0],
        ];

        $totalBet = array_sum(array_column($winners, 'amount'));
        echo "奖池总额: {$prizePool} TRX\n";
        echo "中奖者总投注: {$totalBet} TRX\n\n";

        $this->assertEquals(600.0, $totalBet, '总投注应为600 TRX');

        echo "奖金分配:\n";
        $totalDistributed = 0;
        foreach ($winners as $winner) {
            $ratio = $winner['amount'] / $totalBet;
            $prize = $prizePool * $ratio;
            $totalDistributed += $prize;

            echo "  节点{$winner['node_id']}: 投注{$winner['amount']} TRX";
            echo " (占比" . round($ratio * 100, 2) . "%)";
            echo " → 获得{$prize} TRX\n";

            $this->assertGreaterThan(0, $prize, '奖金应大于0');
        }

        echo "\n总分配奖金: {$totalDistributed} TRX\n";
        $this->assertEquals($prizePool, round($totalDistributed, 2), '总分配金额应等于奖池金额');
    }

    /**
     * 测试Jackpot和范围匹配同时发生
     */
    public function testJackpotAndRangeMatchTogether()
    {
        echo "\n========== 测试Jackpot和范围匹配同时发生 ==========\n";

        $headTicket = '9999888877';
        $matchDigits = 6;

        $bodyTickets = [
            ['node_id' => 1, 'ticket' => '9999888877', 'amount' => 100.0], // Jackpot
            ['node_id' => 2, 'ticket' => '9999880000', 'amount' => 50.0],  // 范围匹配
            ['node_id' => 3, 'ticket' => '9999888800', 'amount' => 60.0],  // 范围匹配
            ['node_id' => 4, 'ticket' => '9999888877', 'amount' => 80.0],  // Jackpot
            ['node_id' => 5, 'ticket' => '1111111111', 'amount' => 40.0],  // 不匹配
        ];

        echo "蛇头票号: {$headTicket}\n";
        echo "匹配位数: {$matchDigits}\n\n";

        $matches = TicketNumberHelper::findMatches($headTicket, $bodyTickets, $matchDigits);

        // 计算奖池
        $totalAmount = array_sum(array_column($bodyTickets, 'amount'));
        $jackpotPool = $totalAmount * 0.70;
        $rangePool = $totalAmount * 0.20;

        echo "总投注: {$totalAmount} TRX\n";
        echo "Jackpot奖池(70%): {$jackpotPool} TRX\n";
        echo "范围匹配奖池(20%): {$rangePool} TRX\n\n";

        echo "Jackpot中奖 (" . count($matches['jackpot']) . "个):\n";
        $jackpotTotalBet = array_sum(array_column($matches['jackpot'], 'amount'));
        foreach ($matches['jackpot'] as $winner) {
            $ratio = $winner['amount'] / $jackpotTotalBet;
            $prize = $jackpotPool * $ratio;
            echo "  节点{$winner['node_id']}: 投注{$winner['amount']} TRX → 获得{$prize} TRX\n";
        }

        echo "\n范围匹配中奖 (" . count($matches['range']) . "个):\n";
        $rangeTotalBet = array_sum(array_column($matches['range'], 'amount'));
        foreach ($matches['range'] as $winner) {
            $ratio = $winner['amount'] / $rangeTotalBet;
            $prize = $rangePool * $ratio;
            echo "  节点{$winner['node_id']}: 投注{$winner['amount']} TRX → 获得{$prize} TRX\n";
        }

        $this->assertCount(2, $matches['jackpot'], '应该有2个Jackpot中奖');
        $this->assertCount(2, $matches['range'], '应该有2个范围匹配中奖');
    }

    /**
     * 测试无中奖场景
     */
    public function testNoWinners()
    {
        echo "\n========== 测试无中奖场景 ==========\n";

        $headTicket = '1111111111';
        $matchDigits = 5;

        $bodyTickets = [
            ['node_id' => 1, 'ticket' => '2222222222', 'amount' => 100.0],
            ['node_id' => 2, 'ticket' => '3333333333', 'amount' => 50.0],
            ['node_id' => 3, 'ticket' => '4444444444', 'amount' => 80.0],
        ];

        echo "蛇头票号: {$headTicket}\n";
        echo "匹配位数: {$matchDigits}\n\n";

        $matches = TicketNumberHelper::findMatches($headTicket, $bodyTickets, $matchDigits);

        echo "Jackpot中奖: " . count($matches['jackpot']) . "个\n";
        echo "范围匹配: " . count($matches['range']) . "个\n";

        $this->assertCount(0, $matches['jackpot'], '应该没有Jackpot中奖');
        $this->assertCount(0, $matches['range'], '应该没有范围匹配');

        echo "\n本轮无中奖，奖池累积到下一轮\n";
    }

    /**
     * 测试边界匹配情况
     */
    public function testEdgeCaseMatching()
    {
        echo "\n========== 测试边界匹配情况 ==========\n";

        $headTicket = '0000000000';
        $matchDigits = 10;

        $bodyTickets = [
            ['node_id' => 1, 'ticket' => '0000000000', 'amount' => 100.0], // 全0完全匹配
            ['node_id' => 2, 'ticket' => '0000000001', 'amount' => 50.0],  // 前9位匹配
            ['node_id' => 3, 'ticket' => '0000000010', 'amount' => 60.0],  // 前8位匹配
        ];

        echo "蛇头票号: {$headTicket}\n";
        echo "匹配位数: {$matchDigits}\n\n";

        foreach ($bodyTickets as $node) {
            $matchCount = TicketNumberHelper::getMatchDigits($headTicket, $node['ticket']);
            echo "  节点{$node['node_id']}: {$node['ticket']} - 匹配{$matchCount}位\n";
        }

        $matches = TicketNumberHelper::findMatches($headTicket, $bodyTickets, $matchDigits);

        $this->assertCount(1, $matches['jackpot'], '全0票号应该有1个Jackpot');
        $this->assertCount(0, $matches['range'], '匹配位数=10时，只有完全匹配，无范围匹配');
    }

    /**
     * 测试不同匹配位数的影响
     */
    public function testDifferentMatchDigits()
    {
        echo "\n========== 测试不同匹配位数的影响 ==========\n";

        $headTicket = '1234567890';
        $bodyTickets = [
            ['node_id' => 1, 'ticket' => '1234567890', 'amount' => 100.0], // 10位匹配
            ['node_id' => 2, 'ticket' => '1234567800', 'amount' => 50.0],  // 7位匹配
            ['node_id' => 3, 'ticket' => '1234500000', 'amount' => 60.0],  // 5位匹配
            ['node_id' => 4, 'ticket' => '1230000000', 'amount' => 40.0],  // 3位匹配
            ['node_id' => 5, 'ticket' => '1200000000', 'amount' => 30.0],  // 2位匹配
        ];

        echo "蛇头票号: {$headTicket}\n\n";

        $testDigits = [3, 5, 7, 10];

        foreach ($testDigits as $digits) {
            echo "匹配位数 = {$digits}:\n";
            $matches = TicketNumberHelper::findMatches($headTicket, $bodyTickets, $digits);

            echo "  Jackpot: " . count($matches['jackpot']) . "个\n";
            echo "  范围匹配: " . count($matches['range']) . "个\n";

            if ($digits === 3) {
                $this->assertCount(1, $matches['jackpot']);
                $this->assertCount(3, $matches['range']);
            } elseif ($digits === 5) {
                $this->assertCount(1, $matches['jackpot']);
                $this->assertCount(2, $matches['range']);
            } elseif ($digits === 7) {
                $this->assertCount(1, $matches['jackpot']);
                $this->assertCount(1, $matches['range']);
            } elseif ($digits === 10) {
                $this->assertCount(1, $matches['jackpot']);
                $this->assertCount(0, $matches['range']);
            }

            echo "\n";
        }

        echo "结论: 匹配位数越高，中奖难度越大，中奖人数越少\n";
    }

    /**
     * 测试大规模蛇身匹配性能
     */
    public function testLargeScaleMatching()
    {
        echo "\n========== 测试大规模蛇身匹配性能 ==========\n";

        $headTicket = '5555555555';
        $matchDigits = 4;

        // 模拟50个蛇身节点
        $bodyTickets = [];
        for ($i = 1; $i <= 50; $i++) {
            $bodyTickets[] = [
                'node_id' => $i,
                'ticket' => str_pad((string)($i * 111), 10, '0', STR_PAD_LEFT),
                'amount' => 10.0 + ($i * 2),
            ];
        }

        // 添加几个匹配的节点
        $bodyTickets[10]['ticket'] = '5555000000'; // 前4位匹配
        $bodyTickets[20]['ticket'] = '5555123456'; // 前4位匹配
        $bodyTickets[30]['ticket'] = '5555555555'; // Jackpot

        echo "蛇身节点数: " . count($bodyTickets) . "\n";
        echo "蛇头票号: {$headTicket}\n";
        echo "匹配位数: {$matchDigits}\n\n";

        $startTime = microtime(true);
        $matches = TicketNumberHelper::findMatches($headTicket, $bodyTickets, $matchDigits);
        $endTime = microtime(true);

        $executionTime = ($endTime - $startTime) * 1000; // 转换为毫秒

        echo "执行时间: " . round($executionTime, 2) . " ms\n";
        echo "Jackpot中奖: " . count($matches['jackpot']) . "个\n";
        echo "范围匹配: " . count($matches['range']) . "个\n";

        $this->assertCount(1, $matches['jackpot']);
        $this->assertCount(2, $matches['range']);
        $this->assertLessThan(100, $executionTime, '50个节点匹配应在100ms内完成');
    }

    /**
     * 测试奖金分配精度
     */
    public function testPrizeDistributionPrecision()
    {
        echo "\n========== 测试奖金分配精度 ==========\n";

        $prizePool = 999.99; // 小数奖池

        $winners = [
            ['node_id' => 1, 'amount' => 33.33],
            ['node_id' => 2, 'amount' => 33.33],
            ['node_id' => 3, 'amount' => 33.34],
        ];

        $totalBet = array_sum(array_column($winners, 'amount'));
        echo "奖池: {$prizePool} TRX\n";
        echo "总投注: {$totalBet} TRX\n\n";

        echo "奖金分配:\n";
        $totalDistributed = 0;
        foreach ($winners as $winner) {
            $ratio = $winner['amount'] / $totalBet;
            $prize = round($prizePool * $ratio, 6); // 保留6位小数
            $totalDistributed += $prize;

            echo "  节点{$winner['node_id']}: 投注{$winner['amount']} TRX → 获得{$prize} TRX\n";
        }

        echo "\n总分配: {$totalDistributed} TRX\n";
        echo "差额: " . abs($prizePool - $totalDistributed) . " TRX\n";

        // 允许0.01的误差
        $this->assertEqualsWithDelta($prizePool, $totalDistributed, 0.01, '总分配金额与奖池金额差额应小于0.01');
    }

    /**
     * 测试匹配算法正确性
     */
    public function testMatchAlgorithmCorrectness()
    {
        echo "\n========== 测试匹配算法正确性 ==========\n";

        $testCases = [
            [
                'head' => '1234567890',
                'body' => '1234567890',
                'digits' => 10,
                'should_match' => true,
                'description' => '完全相同应匹配',
            ],
            [
                'head' => '1234567890',
                'body' => '1234512345',
                'digits' => 5,
                'should_match' => true,
                'description' => '前5位相同应匹配',
            ],
            [
                'head' => '1234567890',
                'body' => '1234567891',
                'digits' => 10,
                'should_match' => false,
                'description' => '最后一位不同不应完全匹配',
            ],
            [
                'head' => '1234567890',
                'body' => '1230000000',
                'digits' => 5,
                'should_match' => false,
                'description' => '只有3位相同，不应匹配(要求5位)',
            ],
        ];

        foreach ($testCases as $case) {
            $result = TicketNumberHelper::isMatch($case['head'], $case['body'], $case['digits']);

            echo "测试: {$case['description']}\n";
            echo "  蛇头: {$case['head']}\n";
            echo "  蛇身: {$case['body']}\n";
            echo "  要求: 前{$case['digits']}位匹配\n";
            echo "  结果: " . ($result ? '✓ 匹配' : '✗ 不匹配') . "\n";

            $this->assertEquals(
                $case['should_match'],
                $result,
                $case['description']
            );

            echo "\n";
        }
    }

    /**
     * 测试真实游戏场景
     */
    public function testRealGameScenario()
    {
        echo "\n========== 测试真实游戏场景 ==========\n";

        // 模拟真实交易哈希
        $realTransactions = [
            '7c9a8f3e4d2b1a0c5e7f9d8a6b4c2e0f',
            'a1b2c3d4e5f6789012345678abcdef01',
            '1234567890abcdef1234567890abcdef',
            '7c9a8f3e4d2b1a0c5e7f9d8a6b4c2e0f', // 重复（Jackpot）
            'f0e1d2c3b4a5968778695a4b3c2d1e0f',
        ];

        echo "模拟5笔交易进入蛇身:\n\n";

        $bodyTickets = [];
        foreach ($realTransactions as $index => $txHash) {
            $ticket = TicketNumberHelper::extractTicketNumber($txHash, 10);
            $bodyTickets[] = [
                'node_id' => $index + 1,
                'ticket' => $ticket,
                'amount' => 10.0 + ($index * 10),
                'tx_hash' => $txHash,
            ];
            echo "节点" . ($index + 1) . ": {$ticket} (TX: " . substr($txHash, 0, 16) . "...)\n";
        }

        // 新交易触发中奖检测
        $newTxHash = '7c9a8f3e4d2b1a0c5e7f9d8a6b4c2e0f';
        $headTicket = TicketNumberHelper::extractTicketNumber($newTxHash, 10);

        echo "\n新交易生成蛇头: {$headTicket}\n";
        echo "触发中奖检测...\n\n";

        $matches = TicketNumberHelper::findMatches($headTicket, $bodyTickets, 5);

        echo "中奖结果:\n";
        echo "  Jackpot: " . count($matches['jackpot']) . "个\n";
        if (!empty($matches['jackpot'])) {
            foreach ($matches['jackpot'] as $winner) {
                echo "    - 节点{$winner['node_id']}, 票号{$winner['ticket']}, 投注{$winner['amount']} TRX\n";
            }
        }

        echo "  范围匹配: " . count($matches['range']) . "个\n";
        if (!empty($matches['range'])) {
            foreach ($matches['range'] as $winner) {
                echo "    - 节点{$winner['node_id']}, 票号{$winner['ticket']}, 投注{$winner['amount']} TRX\n";
            }
        }

        // 计算奖金
        $totalAmount = array_sum(array_column($bodyTickets, 'amount'));
        $jackpotPool = $totalAmount * 0.70;
        $rangePool = $totalAmount * 0.20;

        echo "\n奖池计算:\n";
        echo "  总投注: {$totalAmount} TRX\n";
        echo "  Jackpot奖池: {$jackpotPool} TRX\n";
        echo "  范围匹配奖池: {$rangePool} TRX\n";

        $this->assertGreaterThan(0, count($matches['jackpot']) + count($matches['range']), '真实场景应该有中奖');
    }
}
