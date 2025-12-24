<?php

namespace tests;

use app\lib\helper\TicketNumberHelper;
use PHPUnit\Framework\TestCase;

/**
 * 票号提取工具测试
 */
class TicketNumberHelperTest extends TestCase
{
    /**
     * 测试从交易哈希提取票号
     */
    public function testExtractTicketNumber()
    {
        // 测试用例1：包含数字的哈希
        $txHash1 = 'a1b2c3d4e5f6g7h8i9j0';
        $ticket1 = TicketNumberHelper::extractTicketNumber($txHash1, 10);
        $this->assertEquals(10, strlen($ticket1), '票号长度应为10位');
        $this->assertMatchesRegularExpression('/^\d+$/', $ticket1, '票号应只包含数字');

        // 测试用例2：纯十六进制哈希
        $txHash2 = '3a7d9f2c1e8b4a6c0d5f';
        $ticket2 = TicketNumberHelper::extractTicketNumber($txHash2, 10);
        $this->assertEquals(10, strlen($ticket2), '票号长度应为10位');

        // 测试用例3：真实的TRON交易哈希格式
        $txHash3 = '7c9a8f3e4d2b1a0c5e7f9d8a6b4c2e0f';
        $ticket3 = TicketNumberHelper::extractTicketNumber($txHash3, 10);
        $this->assertEquals(10, strlen($ticket3), '票号长度应为10位');
        $this->assertMatchesRegularExpression('/^\d+$/', $ticket3, '票号应只包含数字');

        echo "\n测试哈希: {$txHash1}\n提取票号: {$ticket1}\n";
        echo "\n测试哈希: {$txHash2}\n提取票号: {$ticket2}\n";
        echo "\n测试哈希: {$txHash3}\n提取票号: {$ticket3}\n";
    }

    /**
     * 测试从指定位置提取票号
     */
    public function testExtractFromPosition()
    {
        $txHash = '1234567890abcdef1234567890';

        // 从第0位开始提取5位
        $ticket1 = TicketNumberHelper::extractFromPosition($txHash, 0, 5);
        $this->assertEquals(5, strlen($ticket1), '票号长度应为5位');

        // 从第5位开始提取5位
        $ticket2 = TicketNumberHelper::extractFromPosition($txHash, 5, 5);
        $this->assertEquals(5, strlen($ticket2), '票号长度应为5位');

        echo "\n从位置0提取5位: {$ticket1}\n";
        echo "从位置5提取5位: {$ticket2}\n";
    }

    /**
     * 测试票号匹配检测
     */
    public function testIsMatch()
    {
        $ticket1 = '1234567890';
        $ticket2 = '1234512345';
        $ticket3 = '9876543210';

        // 测试前5位匹配
        $this->assertTrue(
            TicketNumberHelper::isMatch($ticket1, $ticket2, 5),
            '前5位应该匹配'
        );

        // 测试前10位匹配（完全匹配）
        $this->assertFalse(
            TicketNumberHelper::isMatch($ticket1, $ticket2, 10),
            '前10位不应该匹配'
        );

        // 测试完全不匹配
        $this->assertFalse(
            TicketNumberHelper::isMatch($ticket1, $ticket3, 5),
            '完全不匹配'
        );

        echo "\n票号1: {$ticket1}\n";
        echo "票号2: {$ticket2}\n";
        echo "前5位匹配: " . (TicketNumberHelper::isMatch($ticket1, $ticket2, 5) ? '是' : '否') . "\n";
    }

    /**
     * 测试Jackpot完全匹配
     */
    public function testIsJackpot()
    {
        $ticket1 = '1234567890';
        $ticket2 = '1234567890';
        $ticket3 = '1234567891';

        $this->assertTrue(
            TicketNumberHelper::isJackpot($ticket1, $ticket2),
            '相同票号应该判定为Jackpot'
        );

        $this->assertFalse(
            TicketNumberHelper::isJackpot($ticket1, $ticket3),
            '不同票号不应该判定为Jackpot'
        );

        echo "\nJackpot测试 - 票号1: {$ticket1}, 票号2: {$ticket2}\n";
        echo "是否Jackpot: " . (TicketNumberHelper::isJackpot($ticket1, $ticket2) ? '是' : '否') . "\n";
    }

    /**
     * 测试计算匹配位数
     */
    public function testGetMatchDigits()
    {
        $testCases = [
            ['1234567890', '1234512345', 5],  // 前5位匹配
            ['1234567890', '1234567890', 10], // 完全匹配
            ['1234567890', '9876543210', 0],  // 不匹配
            ['1234567890', '1230000000', 3],  // 前3位匹配
        ];

        foreach ($testCases as [$ticket1, $ticket2, $expected]) {
            $matchDigits = TicketNumberHelper::getMatchDigits($ticket1, $ticket2);
            $this->assertEquals(
                $expected,
                $matchDigits,
                "票号 {$ticket1} 和 {$ticket2} 应该匹配 {$expected} 位"
            );
            echo "\n票号1: {$ticket1}, 票号2: {$ticket2}, 匹配位数: {$matchDigits}\n";
        }
    }

    /**
     * 测试批量查找匹配节点
     */
    public function testFindMatches()
    {
        $headTicket = '1234567890';
        $bodyTickets = [
            ['ticket' => '1234567890', 'node_id' => 1, 'amount' => 100], // Jackpot
            ['ticket' => '1234512345', 'node_id' => 2, 'amount' => 200], // 范围匹配（前5位：12345）
            ['ticket' => '1234599999', 'node_id' => 3, 'amount' => 150], // 范围匹配（前5位：12345）
            ['ticket' => '9876543210', 'node_id' => 4, 'amount' => 180], // 不匹配
            ['ticket' => '1230000000', 'node_id' => 5, 'amount' => 120], // 不匹配（只匹配3位）
        ];

        $matches = TicketNumberHelper::findMatches($headTicket, $bodyTickets, 5);

        echo "\n蛇头票号: {$headTicket}\n";
        echo "Jackpot匹配数量: " . count($matches['jackpot']) . "\n";
        echo "范围匹配数量（前5位）: " . count($matches['range']) . "\n";

        $this->assertCount(1, $matches['jackpot'], '应该有1个Jackpot匹配');
        $this->assertCount(2, $matches['range'], '应该有2个范围匹配');

        $this->assertEquals(1, $matches['jackpot'][0]['node_id'], 'Jackpot节点ID应该是1');
        $this->assertContains(2, array_column($matches['range'], 'node_id'), '范围匹配应包含节点2');
        $this->assertContains(3, array_column($matches['range'], 'node_id'), '范围匹配应包含节点3');
    }

    /**
     * 测试票号格式化
     */
    public function testFormat()
    {
        $ticket = '1234567890';

        // 默认格式（每3位用-分隔）
        $formatted1 = TicketNumberHelper::format($ticket);
        $this->assertEquals('123-456-789-0', $formatted1, '格式化后应为 123-456-789-0');

        // 自定义分隔符
        $formatted2 = TicketNumberHelper::format($ticket, ' ', 2);
        $this->assertEquals('12 34 56 78 90', $formatted2, '格式化后应为 12 34 56 78 90');

        echo "\n原始票号: {$ticket}\n";
        echo "格式化1: {$formatted1}\n";
        echo "格式化2: {$formatted2}\n";
    }

    /**
     * 测试生成序列号
     */
    public function testGenerateSerialNo()
    {
        $ticket = '1234567890';

        $serialNo1 = TicketNumberHelper::generateSerialNo($ticket);
        $this->assertEquals('TK-1234567890', $serialNo1, '默认序列号应为 TK-1234567890');

        $serialNo2 = TicketNumberHelper::generateSerialNo($ticket, 'SNAKE');
        $this->assertEquals('SNAKE-1234567890', $serialNo2, '自定义序列号应为 SNAKE-1234567890');

        echo "\n票号: {$ticket}\n";
        echo "序列号1: {$serialNo1}\n";
        echo "序列号2: {$serialNo2}\n";
    }

    /**
     * 测试票号验证
     */
    public function testIsValid()
    {
        // 有效票号
        $this->assertTrue(
            TicketNumberHelper::isValid('1234567890', 10),
            '10位纯数字应该有效'
        );

        // 长度不匹配
        $this->assertFalse(
            TicketNumberHelper::isValid('12345', 10),
            '长度不匹配应该无效'
        );

        // 包含非数字字符
        $this->assertFalse(
            TicketNumberHelper::isValid('123456789a', 10),
            '包含字母应该无效'
        );

        echo "\n票号验证测试完成\n";
    }

    /**
     * 测试票号比较
     */
    public function testCompare()
    {
        $ticket1 = '1234567890';
        $ticket2 = '9876543210';
        $ticket3 = '1234567890';

        $this->assertEquals(-1, TicketNumberHelper::compare($ticket1, $ticket2), '小于应返回-1');
        $this->assertEquals(1, TicketNumberHelper::compare($ticket2, $ticket1), '大于应返回1');
        $this->assertEquals(0, TicketNumberHelper::compare($ticket1, $ticket3), '相等应返回0');

        echo "\n票号比较: {$ticket1} vs {$ticket2}: " . TicketNumberHelper::compare($ticket1, $ticket2) . "\n";
    }

    /**
     * 测试生成随机票号
     */
    public function testGenerateRandom()
    {
        $ticket1 = TicketNumberHelper::generateRandom(10);
        $this->assertEquals(10, strlen($ticket1), '随机票号长度应为10');
        $this->assertMatchesRegularExpression('/^\d+$/', $ticket1, '随机票号应只包含数字');

        $ticket2 = TicketNumberHelper::generateRandom(10);
        $this->assertNotEquals($ticket1, $ticket2, '两次生成的随机票号应该不同');

        echo "\n随机票号1: {$ticket1}\n";
        echo "随机票号2: {$ticket2}\n";
    }

    /**
     * 测试综合场景：模拟游戏中的实际使用
     */
    public function testRealWorldScenario()
    {
        echo "\n========== 真实游戏场景测试 ==========\n";

        // 模拟10笔交易生成票号
        $transactions = [
            '7c9a8f3e4d2b1a0c5e7f9d8a6b4c2e0f',
            '1234567890abcdef1234567890abcdef',
            'a1b2c3d4e5f6789012345678abcdef01',
            '7c9a8f3e4d2b1a0c5e7f9d8a6b4c2e10', // 与第一笔接近
            'f0e1d2c3b4a5968778695a4b3c2d1e0f',
            '7c9a000000000000000000000000000', // 部分匹配第一笔
            '9876543210fedcba0987654321fedcba',
            '7c9a8f3e4d2b1a0c5e7f9d8a6b4c2e0f', // 完全重复第一笔（Jackpot）
            'abcdef0123456789abcdef0123456789',
            '1111111111111111111111111111111',
        ];

        $tickets = [];
        foreach ($transactions as $index => $txHash) {
            $ticket = TicketNumberHelper::extractTicketNumber($txHash, 10);
            $tickets[] = [
                'ticket' => $ticket,
                'node_id' => $index + 1,
                'tx_hash' => $txHash,
                'amount' => 100 + ($index * 10),
            ];
            echo "交易" . ($index + 1) . " - 票号: {$ticket}\n";
        }

        // 使用最后一笔作为蛇头
        $headTicket = $tickets[count($tickets) - 1]['ticket'];
        $bodyTickets = array_slice($tickets, 0, count($tickets) - 1);

        echo "\n当前蛇头票号: {$headTicket}\n";
        echo "蛇身节点数量: " . count($bodyTickets) . "\n";

        // 查找匹配（假设匹配4位）
        $matches = TicketNumberHelper::findMatches($headTicket, $bodyTickets, 4);

        echo "\n匹配结果:\n";
        echo "- Jackpot (完全匹配): " . count($matches['jackpot']) . " 个\n";
        echo "- 范围匹配 (前4位): " . count($matches['range']) . " 个\n";

        if (count($matches['jackpot']) > 0) {
            echo "\nJackpot中奖节点:\n";
            foreach ($matches['jackpot'] as $node) {
                echo "  节点ID: {$node['node_id']}, 票号: {$node['ticket']}, 金额: {$node['amount']} TRX\n";
            }
        }

        if (count($matches['range']) > 0) {
            echo "\n范围匹配中奖节点:\n";
            foreach ($matches['range'] as $node) {
                echo "  节点ID: {$node['node_id']}, 票号: {$node['ticket']}, 金额: {$node['amount']} TRX\n";
            }
        }

        // 验证测试结果
        $this->assertIsArray($matches, '匹配结果应该是数组');
        $this->assertArrayHasKey('jackpot', $matches, '应该包含jackpot键');
        $this->assertArrayHasKey('range', $matches, '应该包含range键');

        echo "\n========== 场景测试完成 ==========\n";
    }
}
