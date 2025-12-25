<?php

namespace app\lib\helper;

use GuzzleHttp\Client;
use support\Log;

/**
 * Telegram Bot API助手
 * 封装Telegram Bot API调用
 */
class TelegramBotHelper
{
    protected Client $httpClient;
    protected string $botToken;
    protected string $apiUrl;

    /**
     * 单例实例
     * @var static|null
     */
    protected static ?self $instance = null;

    /**
     * @param string $botToken Telegram Bot Token
     */
    public function __construct(string $botToken)
    {
        $this->botToken = $botToken;
        $this->apiUrl = "https://api.telegram.org/bot{$botToken}";

        $this->httpClient = new Client([
            'timeout' => 30,
            'headers' => [
                'Content-Type' => 'application/json',
            ],
        ]);
    }

    /**
     * 获取实例（从环境变量获取Bot Token）
     * @return static
     */
    protected static function getInstance(): self
    {
        if (self::$instance === null) {
            $botToken = getenv('TELEGRAM_BOT_TOKEN') ?: config('app.telegram_bot_token', '');
            if (empty($botToken)) {
                throw new \RuntimeException('Telegram Bot Token未配置，请在环境变量中设置TELEGRAM_BOT_TOKEN');
            }
            self::$instance = new self($botToken);
        }
        return self::$instance;
    }

    /**
     * 发送消息
     * @param int|string $chatId 聊天ID
     * @param string $text 消息文本
     * @param array $options 额外选项
     * @return array|null
     */
    public function sendMessage($chatId, string $text, array $options = []): ?array
    {
        try {
            $params = array_merge([
                'chat_id' => $chatId,
                'text' => $text,
                'parse_mode' => 'HTML', // 默认使用HTML格式
            ], $options);

            $response = $this->httpClient->post($this->apiUrl . '/sendMessage', [
                'json' => $params,
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            if ($data['ok'] ?? false) {
                return $data['result'];
            }

            Log::error("发送Telegram消息失败", [
                'chat_id' => $chatId,
                'error' => $data['description'] ?? 'Unknown error',
            ]);

            return null;
        } catch (\Throwable $e) {
            Log::error("发送Telegram消息异常: " . $e->getMessage(), [
                'chat_id' => $chatId,
            ]);
            return null;
        }
    }

    /**
     * 回复消息
     * @param int|string $chatId 聊天ID
     * @param int $messageId 要回复的消息ID
     * @param string $text 回复文本
     * @param array $options 额外选项
     * @return array|null
     */
    public function replyToMessage($chatId, int $messageId, string $text, array $options = []): ?array
    {
        $options['reply_to_message_id'] = $messageId;
        return $this->sendMessage($chatId, $text, $options);
    }

    /**
     * 发送照片
     * @param int|string $chatId 聊天ID
     * @param string $photo 照片URL或file_id
     * @param string|null $caption 图片说明
     * @param array $options 额外选项
     * @return array|null
     */
    public function sendPhoto($chatId, string $photo, ?string $caption = null, array $options = []): ?array
    {
        try {
            $params = array_merge([
                'chat_id' => $chatId,
                'photo' => $photo,
            ], $options);

            if ($caption) {
                $params['caption'] = $caption;
                $params['parse_mode'] = 'HTML';
            }

            $response = $this->httpClient->post($this->apiUrl . '/sendPhoto', [
                'json' => $params,
            ]);

            $data = json_decode($response->getBody()->getContents(), true);
            return $data['ok'] ? $data['result'] : null;
        } catch (\Throwable $e) {
            Log::error("发送Telegram照片异常: " . $e->getMessage());
            return null;
        }
    }

    /**
     * 发送文档
     * @param int|string $chatId 聊天ID
     * @param string $document 文档URL或file_id
     * @param string|null $caption 文档说明
     * @param array $options 额外选项
     * @return array|null
     */
    public function sendDocument($chatId, string $document, ?string $caption = null, array $options = []): ?array
    {
        try {
            $params = array_merge([
                'chat_id' => $chatId,
                'document' => $document,
            ], $options);

            if ($caption) {
                $params['caption'] = $caption;
            }

            $response = $this->httpClient->post($this->apiUrl . '/sendDocument', [
                'json' => $params,
            ]);

            $data = json_decode($response->getBody()->getContents(), true);
            return $data['ok'] ? $data['result'] : null;
        } catch (\Throwable $e) {
            Log::error("发送Telegram文档异常: " . $e->getMessage());
            return null;
        }
    }

    /**
     * 删除消息
     * @param int|string $chatId 聊天ID
     * @param int $messageId 消息ID
     * @return bool
     */
    public function deleteMessage($chatId, int $messageId): bool
    {
        try {
            $response = $this->httpClient->post($this->apiUrl . '/deleteMessage', [
                'json' => [
                    'chat_id' => $chatId,
                    'message_id' => $messageId,
                ],
            ]);

            $data = json_decode($response->getBody()->getContents(), true);
            return $data['ok'] ?? false;
        } catch (\Throwable $e) {
            Log::error("删除Telegram消息异常: " . $e->getMessage());
            return false;
        }
    }

    /**
     * 获取聊天信息
     * @param int|string $chatId 聊天ID
     * @return array|null
     */
    public function getChat($chatId): ?array
    {
        try {
            $response = $this->httpClient->post($this->apiUrl . '/getChat', [
                'json' => [
                    'chat_id' => $chatId,
                ],
            ]);

            $data = json_decode($response->getBody()->getContents(), true);
            return $data['ok'] ? $data['result'] : null;
        } catch (\Throwable $e) {
            Log::error("获取Telegram聊天信息异常: " . $e->getMessage());
            return null;
        }
    }

    /**
     * 获取聊天成员信息
     * @param int|string $chatId 聊天ID
     * @param int $userId 用户ID
     * @return array|null
     */
    public function getChatMember($chatId, int $userId): ?array
    {
        try {
            $response = $this->httpClient->post($this->apiUrl . '/getChatMember', [
                'json' => [
                    'chat_id' => $chatId,
                    'user_id' => $userId,
                ],
            ]);

            $data = json_decode($response->getBody()->getContents(), true);
            return $data['ok'] ? $data['result'] : null;
        } catch (\Throwable $e) {
            Log::error("获取Telegram聊天成员信息异常: " . $e->getMessage());
            return null;
        }
    }

    /**
     * 检查用户是否是群组管理员
     * @param int|string $chatId 聊天ID
     * @param int $userId 用户ID
     * @return bool
     */
    public function isAdmin($chatId, int $userId): bool
    {
        $member = $this->getChatMember($chatId, $userId);
        if (!$member) {
            return false;
        }

        $status = $member['status'] ?? '';
        return in_array($status, ['creator', 'administrator'], true);
    }

    /**
     * 发送带键盘的消息
     * @param int|string $chatId 聊天ID
     * @param string $text 消息文本
     * @param array $keyboard 键盘按钮数组
     * @param bool $inline 是否为内联键盘
     * @return array|null
     */
    public function sendMessageWithKeyboard($chatId, string $text, array $keyboard, bool $inline = false): ?array
    {
        $replyMarkup = $inline
            ? ['inline_keyboard' => $keyboard]
            : ['keyboard' => $keyboard, 'resize_keyboard' => true];

        return $this->sendMessage($chatId, $text, [
            'reply_markup' => json_encode($replyMarkup),
        ]);
    }

    /**
     * 编辑消息
     * @param int|string $chatId 聊天ID
     * @param int $messageId 消息ID
     * @param string $text 新文本
     * @param array $options 额外选项
     * @return array|null
     */
    public function editMessageText($chatId, int $messageId, string $text, array $options = []): ?array
    {
        try {
            $params = array_merge([
                'chat_id' => $chatId,
                'message_id' => $messageId,
                'text' => $text,
                'parse_mode' => 'HTML',
            ], $options);

            $response = $this->httpClient->post($this->apiUrl . '/editMessageText', [
                'json' => $params,
            ]);

            $data = json_decode($response->getBody()->getContents(), true);
            return $data['ok'] ? $data['result'] : null;
        } catch (\Throwable $e) {
            Log::error("编辑Telegram消息异常: " . $e->getMessage());
            return null;
        }
    }

    /**
     * 发送通知到群组（带格式）
     * @param int|string $chatId 聊天ID
     * @param string $title 通知标题
     * @param string $content 通知内容
     * @param string $type 通知类型（info/success/warning/error）
     * @return array|null
     */
    public function sendNotification($chatId, string $title, string $content, string $type = 'info'): ?array
    {
        $emoji = match ($type) {
            'success' => '✅',
            'warning' => '⚠️',
            'error' => '❌',
            default => 'ℹ️',
        };

        $text = "{$emoji} <b>{$title}</b>\n\n{$content}";
        return $this->sendMessage($chatId, $text);
    }

    /**
     * 批量发送消息
     * @param array $chatIds 聊天ID数组
     * @param string $text 消息文本
     * @return array 发送结果 ['success' => [], 'failed' => []]
     */
    public function broadcastMessage(array $chatIds, string $text): array
    {
        $results = ['success' => [], 'failed' => []];

        foreach ($chatIds as $chatId) {
            $result = $this->sendMessage($chatId, $text);
            if ($result) {
                $results['success'][] = $chatId;
            } else {
                $results['failed'][] = $chatId;
            }

            // 避免触发Telegram速率限制
            usleep(100000); // 0.1秒
        }

        return $results;
    }

    /**
     * 设置Webhook
     * @param string $url Webhook URL
     * @param array $options 额外选项
     * @return bool
     */
    public function setWebhook(string $url, array $options = []): bool
    {
        try {
            $params = array_merge([
                'url' => $url,
            ], $options);

            $response = $this->httpClient->post($this->apiUrl . '/setWebhook', [
                'json' => $params,
            ]);

            $data = json_decode($response->getBody()->getContents(), true);
            return $data['ok'] ?? false;
        } catch (\Throwable $e) {
            Log::error("设置Telegram Webhook异常: " . $e->getMessage());
            return false;
        }
    }

    /**
     * 获取Webhook信息
     * @return array|null
     */
    public function getWebhookInfo(): ?array
    {
        try {
            $response = $this->httpClient->get($this->apiUrl . '/getWebhookInfo');
            $data = json_decode($response->getBody()->getContents(), true);
            return $data['ok'] ? $data['result'] : null;
        } catch (\Throwable $e) {
            Log::error("获取Telegram Webhook信息异常: " . $e->getMessage());
            return null;
        }
    }

    /**
     * 删除Webhook
     * @return bool
     */
    public function deleteWebhook(): bool
    {
        return $this->setWebhook('');
    }

    // ==================== 静态方法包装器 ====================

    /**
     * 静态方法：发送消息（简化调用）
     * @param int|string $chatId 聊天ID
     * @param string $text 消息文本
     * @param array $options 额外选项
     * @return array|null
     */
    public static function send($chatId, string $text, array $options = []): ?array
    {
        return self::getInstance()->sendMessage($chatId, $text, $options);
    }

    /**
     * 静态方法：检查用户是否是管理员
     * @param int|string $chatId 聊天ID
     * @param int $userId 用户ID
     * @return bool
     */
    public static function checkAdmin($chatId, int $userId): bool
    {
        return self::getInstance()->isAdmin($chatId, $userId);
    }

    /**
     * 静态方法：获取聊天成员信息
     * @param int|string $chatId 聊天ID
     * @param int $userId 用户ID
     * @return array|null
     */
    public static function getMember($chatId, int $userId): ?array
    {
        return self::getInstance()->getChatMember($chatId, $userId);
    }

    /**
     * 静态方法：发送通知
     * @param int|string $chatId 聊天ID
     * @param string $title 通知标题
     * @param string $content 通知内容
     * @param string $type 通知类型
     * @return array|null
     */
    public static function notify($chatId, string $title, string $content, string $type = 'info'): ?array
    {
        return self::getInstance()->sendNotification($chatId, $title, $content, $type);
    }
}
