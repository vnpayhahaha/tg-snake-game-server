<?php

namespace tests;

use app\lib\helper\TronWebHelper;
use PHPUnit\Framework\TestCase;

/**
 * TRON Web助手测试
 */
class TronWebHelperTest extends TestCase
{
    /**
     * 测试TRON地址验证
     */
    public function testIsValidAddress()
    {
        // 有效地址测试（真实TRON地址格式：T开头 + 33个Base58字符）
        $validAddresses = [
            'TRX9a5u5sKjDGYJxDQaB4YWR7tJ8YePvUV',
            'TR7NHqjeKQxGTCi8q8ZY4pL8otSzgjLj6t',
            'TYs5fCEqMatiPGWTb67rhqfReGDE8yHmWC',
        ];

        foreach ($validAddresses as $address) {
            $this->assertTrue(
                TronWebHelper::isValidAddress($address),
                "地址 {$address} 应该是有效的TRON地址"
            );
        }

        echo "\n有效TRON地址测试:\n";
        foreach ($validAddresses as $address) {
            echo "  {$address}: " . (TronWebHelper::isValidAddress($address) ? '✓ 有效' : '✗ 无效') . "\n";
        }

        // 无效地址测试
        $invalidAddresses = [
            'ABC123',                                    // 太短
            'T12345',                                    // 太短
            'BTC1234567890123456789012345678901234',   // 不以T开头
            'T123456789012345678901234567890123456',   // 太长（35个字符）
            'T12345678901234567890123456789012',       // 太短（33个字符）
            'T@#$%^&*()',                               // 包含特殊字符
        ];

        foreach ($invalidAddresses as $address) {
            $this->assertFalse(
                TronWebHelper::isValidAddress($address),
                "地址 {$address} 应该是无效的TRON地址"
            );
        }

        echo "\n无效TRON地址测试:\n";
        foreach ($invalidAddresses as $address) {
            echo "  {$address}: " . (TronWebHelper::isValidAddress($address) ? '✓ 有效' : '✗ 无效') . "\n";
        }
    }

    /**
     * 测试SUN到TRX的转换
     */
    public function testSunToTrx()
    {
        $testCases = [
            [1000000, 1.0],       // 1 TRX = 1,000,000 SUN
            [500000, 0.5],        // 0.5 TRX
            [1500000, 1.5],       // 1.5 TRX
            [100, 0.0001],        // 0.0001 TRX
            [0, 0.0],             // 0 TRX
            [10000000, 10.0],     // 10 TRX
        ];

        echo "\nSUN到TRX转换测试:\n";
        foreach ($testCases as [$sun, $expectedTrx]) {
            $actualTrx = TronWebHelper::sunToTrx($sun);
            $this->assertEquals(
                $expectedTrx,
                $actualTrx,
                "{$sun} SUN 应该等于 {$expectedTrx} TRX"
            );
            echo "  {$sun} SUN = {$actualTrx} TRX\n";
        }
    }

    /**
     * 测试TRX到SUN的转换
     */
    public function testTrxToSun()
    {
        $testCases = [
            [1.0, 1000000],       // 1 TRX = 1,000,000 SUN
            [0.5, 500000],        // 0.5 TRX
            [1.5, 1500000],       // 1.5 TRX
            [0.0001, 100],        // 0.0001 TRX
            [0.0, 0],             // 0 TRX
            [10.0, 10000000],     // 10 TRX
            [99.99, 99990000],    // 99.99 TRX
        ];

        echo "\nTRX到SUN转换测试:\n";
        foreach ($testCases as [$trx, $expectedSun]) {
            $actualSun = TronWebHelper::trxToSun($trx);
            $this->assertEquals(
                $expectedSun,
                $actualSun,
                "{$trx} TRX 应该等于 {$expectedSun} SUN"
            );
            echo "  {$trx} TRX = {$actualSun} SUN\n";
        }
    }

    /**
     * 测试单位转换的往返转换
     */
    public function testRoundTripConversion()
    {
        $testValues = [1.0, 0.5, 10.5, 99.99, 0.0001];

        echo "\n往返转换测试（TRX -> SUN -> TRX）:\n";
        foreach ($testValues as $originalTrx) {
            $sun = TronWebHelper::trxToSun($originalTrx);
            $backToTrx = TronWebHelper::sunToTrx($sun);

            $this->assertEquals(
                $originalTrx,
                $backToTrx,
                "往返转换后应该得到原始值 {$originalTrx}"
            );

            echo "  {$originalTrx} TRX -> {$sun} SUN -> {$backToTrx} TRX\n";
        }
    }

    /**
     * 测试解析交易数据（模拟）
     */
    public function testParseTransactionStructure()
    {
        // 模拟TRON交易数据结构
        $mockTransaction = [
            'txID' => '7c9a8f3e4d2b1a0c5e7f9d8a6b4c2e0f1234567890abcdef1234567890abcdef',
            'block_number' => 12345678,
            'block_timestamp' => 1703001234000,
            'raw_data' => [
                'contract' => [
                    [
                        'type' => 'TransferContract',
                        'parameter' => [
                            'value' => [
                                'owner_address' => 'TSenderAddress123456789012345678901',
                                'to_address' => 'TReceiverAddr123456789012345678901',
                                'amount' => 1000000, // 1 TRX in SUN
                            ],
                        ],
                    ],
                ],
            ],
            'ret' => [
                ['contractRet' => 'SUCCESS'],
            ],
        ];

        // 验证交易数据结构
        $this->assertArrayHasKey('txID', $mockTransaction, '交易应该有txID');
        $this->assertArrayHasKey('block_number', $mockTransaction, '交易应该有block_number');
        $this->assertArrayHasKey('raw_data', $mockTransaction, '交易应该有raw_data');

        echo "\n模拟TRON交易数据结构测试:\n";
        echo "  交易哈希: {$mockTransaction['txID']}\n";
        echo "  区块高度: {$mockTransaction['block_number']}\n";

        $amount = $mockTransaction['raw_data']['contract'][0]['parameter']['value']['amount'];
        $trxAmount = TronWebHelper::sunToTrx($amount);
        echo "  转账金额: {$amount} SUN ({$trxAmount} TRX)\n";
    }

    /**
     * 测试批量金额转换
     */
    public function testBatchAmountConversion()
    {
        $transactions = [
            ['amount_sun' => 1000000, 'description' => '小额转账'],
            ['amount_sun' => 10000000, 'description' => '中额转账'],
            ['amount_sun' => 100000000, 'description' => '大额转账'],
            ['amount_sun' => 500000, 'description' => '小数金额'],
        ];

        echo "\n批量金额转换测试:\n";
        foreach ($transactions as $tx) {
            $trx = TronWebHelper::sunToTrx($tx['amount_sun']);
            echo "  {$tx['description']}: {$tx['amount_sun']} SUN = {$trx} TRX\n";

            $this->assertIsFloat($trx, '转换结果应该是浮点数');
            $this->assertGreaterThanOrEqual(0, $trx, '转换结果应该大于等于0');
        }
    }

    /**
     * 测试常见游戏金额场景
     */
    public function testGameScenarioAmounts()
    {
        echo "\n游戏场景金额测试:\n";

        // 最小投注金额
        $minBet = 10.0; // TRX
        $minBetSun = TronWebHelper::trxToSun($minBet);
        echo "  最小投注: {$minBet} TRX = {$minBetSun} SUN\n";
        $this->assertEquals(10000000, $minBetSun);

        // 典型投注金额
        $typicalBets = [20, 50, 100, 500];
        echo "  典型投注金额:\n";
        foreach ($typicalBets as $bet) {
            $sun = TronWebHelper::trxToSun($bet);
            echo "    {$bet} TRX = {$sun} SUN\n";
        }

        // 奖金计算示例
        $prizePool = 10000.0; // TRX
        $jackpotRatio = 0.70; // 70%
        $rangeRatio = 0.20;   // 20%
        $platformRatio = 0.10; // 10%

        $jackpotAmount = $prizePool * $jackpotRatio;
        $rangeAmount = $prizePool * $rangeRatio;
        $platformAmount = $prizePool * $platformRatio;

        echo "\n  奖池分配示例（总奖池: {$prizePool} TRX）:\n";
        echo "    Jackpot（70%）: {$jackpotAmount} TRX\n";
        echo "    范围匹配（20%）: {$rangeAmount} TRX\n";
        echo "    平台费（10%）: {$platformAmount} TRX\n";

        // 验证比例总和
        $this->assertEqualsWithDelta(1.0, $jackpotRatio + $rangeRatio + $platformRatio, 0.0001);

        // 验证金额总和
        $totalDistributed = $jackpotAmount + $rangeAmount + $platformAmount;
        $this->assertEqualsWithDelta($prizePool, $totalDistributed, 0.01);
    }

    /**
     * 测试交易验证逻辑
     */
    public function testTransactionValidation()
    {
        echo "\n交易验证逻辑测试:\n";

        $minBetAmount = 10.0; // TRX
        $testTransactions = [
            ['amount' => 15.0, 'valid' => true, 'reason' => '大于最小投注'],
            ['amount' => 10.0, 'valid' => true, 'reason' => '等于最小投注'],
            ['amount' => 5.0, 'valid' => false, 'reason' => '小于最小投注'],
            ['amount' => 0.0, 'valid' => false, 'reason' => '零金额'],
            ['amount' => 100.0, 'valid' => true, 'reason' => '正常投注'],
        ];

        foreach ($testTransactions as $tx) {
            $isValid = $tx['amount'] >= $minBetAmount;
            $this->assertEquals(
                $tx['valid'],
                $isValid,
                "金额 {$tx['amount']} TRX 的验证结果应该是 " . ($tx['valid'] ? '有效' : '无效')
            );

            $status = $isValid ? '✓ 有效' : '✗ 无效';
            echo "  {$tx['amount']} TRX: {$status} ({$tx['reason']})\n";
        }
    }

    /**
     * 测试地址格式规范
     */
    public function testAddressFormatStandards()
    {
        echo "\nTRON地址格式规范测试:\n";

        // 测试地址长度
        $address = 'T' . str_repeat('A', 33); // 正确的长度
        $this->assertTrue(
            TronWebHelper::isValidAddress($address),
            '34位以T开头的地址应该有效'
        );

        // 测试首字母
        $validFirstChar = ['T'];
        foreach ($validFirstChar as $char) {
            $addr = $char . str_repeat('1', 33);
            $result = TronWebHelper::isValidAddress($addr);
            echo "  首字母 '{$char}': " . ($result ? '✓ 有效' : '✗ 无效') . "\n";
        }

        // 测试无效首字母
        $invalidFirstChars = ['B', 'E', '1', 'X'];
        foreach ($invalidFirstChars as $char) {
            $addr = $char . str_repeat('1', 33);
            $result = TronWebHelper::isValidAddress($addr);
            $this->assertFalse($result, "首字母 '{$char}' 的地址应该无效");
        }
    }
}
