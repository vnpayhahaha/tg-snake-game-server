<?php

namespace app\tools;

use InvalidArgumentException;

class Base62Converter
{
    // 定义62进制字符集（0-9, A-Z, a-z）
    private static string $chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';

    /**
     * 62进制转10进制
     * @param string $base62 62进制字符串
     * @return int 10进制数值
     */
    public static function base62ToDec(string $base62): int
    {
        $dec = 0;
        $chars = str_split($base62);
        $chars = array_reverse($chars); // 反转数组方便计算

        foreach ($chars as $power => $char) {
            $index = strpos(self::$chars, $char);
            if ($index === false) {
                throw new InvalidArgumentException("Invalid character: $char");
            }
            $dec += $index * (62 ** $power);
        }
        return $dec;
    }

    /**
     * 10进制转62进制
     * @param int $dec 10进制数值
     * @param int $fill_length 填充长度
     * @return string 62进制字符串
     */
    public static function decToBase62(int $dec, int $fill_length = 0): string
    {
        if ($dec < 0) {
            throw new InvalidArgumentException("Negative number not supported");
        }

        $base62 = '';
        do {
            $remainder = $dec % 62;
            $base62 = self::$chars[$remainder] . $base62;
            $dec = (int)($dec / 62);
        } while ($dec > 0);

        $result = $base62 ?: '0'; // 处理输入为0的情况
        return str_pad($result, $fill_length, '0', STR_PAD_LEFT);
    }
}

