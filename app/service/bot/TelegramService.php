<?php

namespace app\service\bot;

use app\repository\TelegramCommandMessageRecordRepository;
use DI\Attribute\Inject;
use JsonException;
use Telegram as TelegramBot;
use Webman\RedisQueue\Redis;

class TelegramService
{
    private TelegramBot $telegramBot;

    #[Inject]
    protected TelegramCommandService $commandService;

    #[Inject]
    protected TelegramCommandMessageRecordRepository $telegramCommandMessageRecordRepository;

    public function __construct()
    {
        var_dump('====init==TELEGRAM_TOKEN===', env('TELEGRAM_TOKEN'));
        $this->telegramBot = new TelegramBot(env('TELEGRAM_TOKEN'));
    }

    public function notify($url)
    {
        return $this->telegramBot->setWebhook($url);
    }


    // 监听webHook消息
    public function webHook(array $params): bool
    {
        var_dump('======webHook params===', json_encode($params, JSON_UNESCAPED_UNICODE));
        $this->telegramBot->setData($params);
        $this->commandService->setTelegramBot($this->telegramBot);
        //把信息进行分类，分开是私人聊天还是群内聊天
        $is_group = $this->telegramBot->messageFromGroup();
        var_dump('======webHook $is_group===', $is_group);
        if ($is_group) {
            $chat_id = (int)$this->telegramBot->ChatID();
            try {
                $this->groupWork();
            } catch (\Throwable $e) {
                return $this->sendMessageProducer($chat_id, $e->getMessage(), $this->telegramBot->MessageID());
            }
        } else {
            $this->privateWork();
        }
        return true;
    }


    public function groupWork(): bool
    {
        $text = $this->telegramBot->Text();
        $chat_id = (int)$this->telegramBot->ChatID();
        $type = $this->telegramBot->getUpdateType();
        var_dump('======webHook $type===', $type);
        var_dump('======webHook $text===', $text);
        // 如果 text 是 / 开头的，则尝试查询commandService对应的方法，空格后面的参数会作为参数传入commandService对应的方法
        // eg: /bind daxiong 18 185cm  $this->commandService->bind(['daxiong','18','185cm'])
        if ($type === TelegramBot::MESSAGE && filled($text) && str_starts_with($text, '/')) {
            try {
                $commandOriginal = substr($text, 1);
                $params = explode('@', $commandOriginal);
                var_dump('$params=@===', $params);
                if ($this->commandRunProducer($params)) {
                    return true;
                }
                // 采用 PHP_EOL 换行符分割
                $params = explode(PHP_EOL, $commandOriginal);
                var_dump('$params=换行符===', $params);
                if ($this->commandRunProducer($params)) {
                    return true;
                }
                $params = explode(' ', $commandOriginal);
                var_dump('$params=空格===', $params);
                if ($this->commandRunProducer($params)) {
                    return true;
                }
            } catch (\Throwable $e) {
                return $this->sendMessageProducer($chat_id, [
                    'Execute command exception:',
                    $e->getMessage(),
                ], $this->telegramBot->MessageID());
            }
            return $this->sendMessageProducer($chat_id, [
                'Unknown commands, you can obtain command information through /help!',
                '未知指令,可通过[/帮助]获取指令信息!',
            ], $this->telegramBot->MessageID());
        }

        if ($type === TelegramBot::PHOTO) {
            try {
                $this->telegramCommandMessageRecordRepository->getModel()->firstOrCreate([
                    'chat_id'    => $this->telegramBot->ChatID(),
                    'message_id' => $this->telegramBot->MessageID(),
                ], [
                    'command'          => 'writeOffOrderByPhoto',
                    'chat_name'        => $this->telegramBot->getGroupTitle(),
                    'user_id'          => $this->telegramBot->UserId(),
                    'username'         => $this->telegramBot->UserName(),
                    'nickname'         => $this->telegramBot->FirstName() . ' ' . $this->telegramBot->LastName(),
                    'original_message' => $this->telegramBot->Text(),
                ]);
                $replyID = $this->telegramBot->MessageID() ?? 0;
            } catch (\Throwable $e) {
                $replyID = 0;
            }
            return $this->sendMessageProducer($chat_id, $this->commandService->writeOffOrderByPhoto(), $replyID);
        }

        return false;
    }

    private function commandRunProducer(array $params): bool
    {
        $firstParam = array_shift($params);
        $command = strtolower(trim($firstParam));
        if (!CommandEnum::isCommand($command)) {
            return false;
        }
        $method = CommandEnum::getCommand($command);
        var_dump('$method=', $method);
        // 过滤空并重置索引
        $params = array_filter($params);
        // trim
        $params = array_map('trim', array_values($params));
        if (method_exists($this->commandService, $method)) {
            $data = [
                'data'    => $this->telegramBot->getData(),
                'params'  => $params,
                'method'  => $method,
                'command' => $command,
            ];
            return Redis::send(CommandEnum::TELEGRAM_COMMAND_RUN_QUEUE_NAME, $data);
        }
        return false;
    }

    public function commandRunConsumer(array $data): bool
    {
        if (!isset($data['data'], $data['params'], $data['method'], $data['command'])) {
            var_dump('commandGroupRunConsumer params error=', $data);
            return false;
        }
        $this->telegramBot->setData($data['data']);
        $this->commandService->setTelegramBot($this->telegramBot);
        $record = $this->telegramCommandMessageRecordRepository->getModel()->firstOrCreate([
            'chat_id'    => $this->telegramBot->ChatID(),
            'message_id' => $this->telegramBot->MessageID(),
        ], [
            'command'          => $data['command'],
            'chat_name'        => $this->telegramBot->getGroupTitle(),
            'user_id'          => $this->telegramBot->UserId(),
            'username'         => $this->telegramBot->UserName() ?? '',
            'nickname'         => $this->telegramBot->FirstName() . ' ' . $this->telegramBot->LastName(),
            'original_message' => $this->telegramBot->Text(),
        ]);
        try {
            $result = $this->commandService->{$data['method']}($this->telegramBot->UserId(), $data['params'], $record->id);
        } catch (\Throwable $e) {
            var_dump('------throwable==commandGroupRun--', $e->getMessage());
            return $this->returnException($this->telegramBot->ChatID(), $e, $record->id);
        }
        return $this->sendMessageProducer($this->telegramBot->ChatID(), $result, $this->telegramBot->MessageID());
    }

    public function privateWork(): void
    {
        $text = $this->telegramBot->Text();
        $chat_id = (int)$this->telegramBot->ChatID();
        $type = $this->telegramBot->getUpdateType();
        if ($text === '/start') {
            try {
                $list[] = '⏰️ Welcome to use this system';
                $list[] = '⏰️ 欢迎你使用本系统';
                $list[] = 'nickname：<code>' . $this->telegramBot->FirstName() . ' ' . $this->telegramBot->LastName() . '</code>';
                $list[] = 'username：<code>' . $this->telegramBot->UserName() . '</code>';
                $list[] = 'userID：<code>' . $this->telegramBot->UserId() . '</code>';

                $this->sendMessageProducer($chat_id, $list);
                return;

            } catch (\Exception $e) {
                $this->returnException($chat_id, $e);

            }
        }
    }

    /**格式化文字
     * @param array $array
     * @return string
     */
    public static function formatTxt(array $array): string
    {
        $text = '';
        foreach ($array as $item) {
            if ($text === '') {
                $text = $item;
            } else {
                $text .= PHP_EOL . $item;
            }
        }
        return $text;
    }

    /**群内回复异常
     * @param $chat_id
     * @param $e
     * @param $token
     * @return void
     */
    public function returnException($chat_id, $e, $recordID = 0): bool
    {
        $reply = 'Exception info：' . PHP_EOL . $e->getMessage() . PHP_EOL . 'LINE:' . $e->getLine() . PHP_EOL . 'Trace:' . PHP_EOL . $e->getTraceAsString();
        if ($recordID > 0) {
            $this->telegramCommandMessageRecordRepository->getModel()->where([
                'chat_id'    => $chat_id,
                'message_id' => $this->telegramBot->MessageID(),
            ])->update([
                'response_message' => $reply,
                'status'           => 3
            ]);
        }
        return $this->sendMessageProducer($chat_id, $reply);
    }

    /**
     * @param int $chat_id
     * @param mixed $content
     * @param int $reply_markup
     * @return bool
     * @throws JsonException
     */
    public function sendMessageProducer(int $chat_id, mixed $content, int $reply_markup = 0): bool
    {
        if (is_array($content)) {
            $content = self::formatTxt($content);
        } else if (is_string($content)) {
            $content = trim($content);
        } else if (is_numeric($content)) {
            $content = (string)$content;
        } else if (is_object($content)) {
            $content = json_encode($content, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
        } else if (is_bool($content)) {
            $content = $content ? 'successful' : 'failed';
        } else {
            return false;
        }
        $data = array(
            'chat_id'    => $chat_id,
            'text'       => $content,
            'parse_mode' => 'HTML'
        );
        if ($reply_markup > 0) {
            $data['reply_to_message_id'] = $reply_markup;
        }
        return Redis::send(CommandEnum::TELEGRAM_NOTICE_QUEUE_NAME, $data);
    }

    /**
     * @param array $data
     *  - string int
     *  - string text
     *  - string parse_mode HTML
     */
    public function sendMessageConsumer(array $data)
    {
        $message_id = $data['reply_to_message_id'] ?? 0;
        if ($message_id > 0) {
            $data['text'] = '[Reply|回复]' . PHP_EOL . $data['text'];
            $this->telegramCommandMessageRecordRepository->getModel()->where([
                'chat_id'    => $data['chat_id'],
                'message_id' => $message_id,
            ])->update([
                'response_message' => $data['text'],
                'status'           => 2
            ]);
        }

        return $this->telegramBot->sendMessage($data);
    }
}