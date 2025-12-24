<?php

namespace app\lib\cache;

use app\lib\annotation\Cacheable;
use app\process\CacheableProcessor;
use support\Log;
use Webman\Event\Event;


class CacheEventService
{
    /**
     * 触发缓存更新事件
     */
    public static function trigger(string $listener, ...$params): void
    {
        $listener = CacheableProcessor::normalizeListenerName($listener);
        // 统一转为小写避免大小写问题
        $listener = strtolower($listener);

        Log::debug("Triggering cache event: $listener with params: " . json_encode($params));

        try {
            Event::dispatch($listener, $params);
        } catch (\Throwable $e) {
            Log::error("Error triggering event $listener: {$e->getMessage()}");
        }
    }

    /**
     * 从方法获取监听器并触发
     */
    public static function triggerForMethod($instance, string $method, ...$params): void
    {
        try {
            $refMethod = new \ReflectionMethod($instance, $method);
            $attributes = $refMethod->getAttributes(Cacheable::class);

            if (!empty($attributes)) {
                /** @var Cacheable $cacheable */
                $cacheable = $attributes[0]->newInstance();

                if ($cacheable->listener) {
                    // 修复：正确传递参数
                    self::trigger($cacheable->listener, ...$params);
                }
            }
        } catch (\Throwable $e) {
            Log::error("Failed to trigger cache event: {$e->getMessage()}");
        }
    }
}
