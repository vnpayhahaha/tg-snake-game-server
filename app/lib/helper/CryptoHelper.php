<?php

namespace app\lib\helper;

use support\Log;

/**
 * 加密解密助手
 * 使用AES-256-CBC加密算法
 */
class CryptoHelper
{
    /**
     * 加密方法
     */
    private const CIPHER_METHOD = 'AES-256-CBC';

    /**
     * 获取加密密钥
     * @return string
     */
    private static function getEncryptionKey(): string
    {
        $key = getenv('APP_ENCRYPTION_KEY') ?: config('app.encryption_key', '');

        if (empty($key)) {
            throw new \RuntimeException('加密密钥未配置，请在.env中设置APP_ENCRYPTION_KEY');
        }

        // 确保密钥长度为32字节（AES-256需要）
        return hash('sha256', $key, true);
    }

    /**
     * 加密数据
     * @param string $plainText 明文
     * @return string 加密后的数据（base64编码）
     */
    public static function encrypt(string $plainText): string
    {
        if (empty($plainText)) {
            return '';
        }

        try {
            $key = self::getEncryptionKey();

            // 生成随机IV
            $ivLength = openssl_cipher_iv_length(self::CIPHER_METHOD);
            $iv = openssl_random_pseudo_bytes($ivLength);

            // 加密数据
            $encrypted = openssl_encrypt(
                $plainText,
                self::CIPHER_METHOD,
                $key,
                OPENSSL_RAW_DATA,
                $iv
            );

            if ($encrypted === false) {
                throw new \RuntimeException('加密失败');
            }

            // 将IV和加密数据组合后进行base64编码
            return base64_encode($iv . $encrypted);

        } catch (\Throwable $e) {
            Log::error("加密数据失败: " . $e->getMessage());
            throw new \RuntimeException('加密失败: ' . $e->getMessage());
        }
    }

    /**
     * 解密数据
     * @param string $encryptedText 加密的数据（base64编码）
     * @return string 解密后的明文
     */
    public static function decrypt(string $encryptedText): string
    {
        if (empty($encryptedText)) {
            return '';
        }

        try {
            $key = self::getEncryptionKey();

            // base64解码
            $data = base64_decode($encryptedText, true);
            if ($data === false) {
                throw new \RuntimeException('Base64解码失败');
            }

            // 分离IV和加密数据
            $ivLength = openssl_cipher_iv_length(self::CIPHER_METHOD);
            $iv = substr($data, 0, $ivLength);
            $encrypted = substr($data, $ivLength);

            // 解密数据
            $decrypted = openssl_decrypt(
                $encrypted,
                self::CIPHER_METHOD,
                $key,
                OPENSSL_RAW_DATA,
                $iv
            );

            if ($decrypted === false) {
                throw new \RuntimeException('解密失败');
            }

            return $decrypted;

        } catch (\Throwable $e) {
            Log::error("解密数据失败: " . $e->getMessage());
            throw new \RuntimeException('解密失败: ' . $e->getMessage());
        }
    }

    /**
     * 验证加密密钥是否已配置
     * @return bool
     */
    public static function isKeyConfigured(): bool
    {
        try {
            self::getEncryptionKey();
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * 生成随机加密密钥（用于初始化配置）
     * @return string
     */
    public static function generateKey(): string
    {
        return bin2hex(random_bytes(32));
    }
}
