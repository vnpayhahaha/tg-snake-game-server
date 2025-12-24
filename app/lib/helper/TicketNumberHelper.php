<?php

namespace app\lib\helper;

/**
 * 票号提取助手
 * 从交易哈希中提取票号数字
 */
class TicketNumberHelper
{
    /**
     * 从交易哈希中提取票号
     * 默认提取前10位数字
     *
     * @param string $txHash 交易哈希
     * @param int $length 票号长度
     * @return string 票号（纯数字）
     */
    public static function extractTicketNumber(string $txHash, int $length = 10): string
    {
        // 移除所有非数字字符
        $digits = preg_replace('/[^0-9]/', '', $txHash);

        // 如果提取的数字不足，从十六进制转换补充
        if (strlen($digits) < $length) {
            $digits = self::hexToDigits($txHash, $length);
        }

        // 截取指定长度
        return substr($digits, 0, $length);
    }

    /**
     * 将十六进制哈希转换为数字字符串
     * 通过累加十六进制字符的数值
     *
     * @param string $txHash 交易哈希
     * @param int $length 目标长度
     * @return string 数字字符串
     */
    protected static function hexToDigits(string $txHash, int $length): string
    {
        $hash = strtolower($txHash);
        $digits = '';

        // 遍历哈希中的每个字符
        for ($i = 0; $i < strlen($hash) && strlen($digits) < $length; $i++) {
            $char = $hash[$i];
            if (ctype_xdigit($char)) {
                $value = hexdec($char);
                $digits .= $value;
            }
        }

        // 如果还是不够，使用哈希的数值
        while (strlen($digits) < $length) {
            $digits .= mt_rand(0, 9);
        }

        return $digits;
    }

    /**
     * 从交易哈希中提取自定义位置的票号
     * 支持指定起始位置和长度
     *
     * @param string $txHash 交易哈希
     * @param int $start 起始位置
     * @param int $length 长度
     * @return string 票号
     */
    public static function extractFromPosition(string $txHash, int $start, int $length): string
    {
        $fullTicket = self::extractTicketNumber($txHash, 50); // 先提取足够长的数字
        return substr($fullTicket, $start, $length);
    }

    /**
     * 从交易哈希中提取多个片段组成票号
     * 例如：取前3位 + 中间3位 + 最后4位
     *
     * @param string $txHash 交易哈希
     * @param array $segments 片段配置 [[start, length], ...]
     * @return string 组合票号
     */
    public static function extractSegments(string $txHash, array $segments): string
    {
        $fullTicket = self::extractTicketNumber($txHash, 50);
        $result = '';

        foreach ($segments as $segment) {
            [$start, $length] = $segment;
            $result .= substr($fullTicket, $start, $length);
        }

        return $result;
    }

    /**
     * 验证票号是否匹配
     * 比较两个票号的指定位数
     *
     * @param string $ticket1 票号1
     * @param string $ticket2 票号2
     * @param int $matchDigits 匹配位数
     * @return bool 是否匹配
     */
    public static function isMatch(string $ticket1, string $ticket2, int $matchDigits): bool
    {
        $t1 = substr($ticket1, 0, $matchDigits);
        $t2 = substr($ticket2, 0, $matchDigits);
        return $t1 === $t2;
    }

    /**
     * 检查完全匹配（Jackpot）
     *
     * @param string $ticket1 票号1
     * @param string $ticket2 票号2
     * @return bool 是否完全匹配
     */
    public static function isJackpot(string $ticket1, string $ticket2): bool
    {
        return $ticket1 === $ticket2;
    }

    /**
     * 计算两个票号的匹配位数
     *
     * @param string $ticket1 票号1
     * @param string $ticket2 票号2
     * @return int 匹配的位数
     */
    public static function getMatchDigits(string $ticket1, string $ticket2): int
    {
        $maxLength = min(strlen($ticket1), strlen($ticket2));
        $matchCount = 0;

        for ($i = 0; $i < $maxLength; $i++) {
            if ($ticket1[$i] === $ticket2[$i]) {
                $matchCount++;
            } else {
                break;
            }
        }

        return $matchCount;
    }

    /**
     * 批量从蛇身中查找匹配的节点
     *
     * @param string $headTicket 蛇头票号
     * @param array $bodyTickets 蛇身票号数组 [['ticket' => '1234567890', 'node_id' => 1], ...]
     * @param int $matchDigits 匹配位数
     * @return array 匹配的节点数组
     */
    public static function findMatches(string $headTicket, array $bodyTickets, int $matchDigits): array
    {
        $matches = [
            'jackpot' => [], // 完全匹配
            'range' => [],   // 范围匹配
        ];

        foreach ($bodyTickets as $node) {
            $bodyTicket = $node['ticket'];

            // 检查完全匹配
            if (self::isJackpot($headTicket, $bodyTicket)) {
                $matches['jackpot'][] = $node;
            }
            // 检查范围匹配
            elseif (self::isMatch($headTicket, $bodyTicket, $matchDigits)) {
                $matches['range'][] = $node;
            }
        }

        return $matches;
    }

    /**
     * 格式化票号显示
     * 添加分隔符使其更易读
     *
     * @param string $ticket 票号
     * @param string $separator 分隔符
     * @param int $groupSize 分组大小
     * @return string 格式化后的票号
     */
    public static function format(string $ticket, string $separator = '-', int $groupSize = 3): string
    {
        return implode($separator, str_split($ticket, $groupSize));
    }

    /**
     * 生成票号序列号
     * 基于票号生成唯一的序列号（用于展示）
     *
     * @param string $ticket 票号
     * @param string $prefix 前缀
     * @return string 序列号（如 TK-1234567890）
     */
    public static function generateSerialNo(string $ticket, string $prefix = 'TK'): string
    {
        return $prefix . '-' . $ticket;
    }

    /**
     * 验证票号格式
     *
     * @param string $ticket 票号
     * @param int $expectedLength 预期长度
     * @return bool 是否有效
     */
    public static function isValid(string $ticket, int $expectedLength): bool
    {
        // 检查是否为纯数字
        if (!ctype_digit($ticket)) {
            return false;
        }

        // 检查长度
        if (strlen($ticket) !== $expectedLength) {
            return false;
        }

        return true;
    }

    /**
     * 比较票号大小（用于排序）
     *
     * @param string $ticket1 票号1
     * @param string $ticket2 票号2
     * @return int -1/0/1
     */
    public static function compare(string $ticket1, string $ticket2): int
    {
        $num1 = (int)$ticket1;
        $num2 = (int)$ticket2;

        if ($num1 === $num2) {
            return 0;
        }

        return $num1 < $num2 ? -1 : 1;
    }

    /**
     * 生成测试票号（用于开发测试）
     *
     * @param int $length 长度
     * @return string 随机票号
     */
    public static function generateRandom(int $length = 10): string
    {
        $ticket = '';
        for ($i = 0; $i < $length; $i++) {
            $ticket .= mt_rand(0, 9);
        }
        return $ticket;
    }
}
