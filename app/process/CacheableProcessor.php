<?php
// app/process/CacheableProcessor.php

namespace app\process;

use app\lib\annotation\Cacheable;
use JetBrains\PhpStorm\ArrayShape;
use ReflectionMethod;
use support\Cache;
use support\Log;
use Webman\Event\Event;

class CacheableProcessor
{
    // 收集缓存统计信息
    private static array $collection = [];
    private static array $listeners  = [];

    // 定义注解缓存前缀（兼容所有存储类型）
    public const ANNOTATION_PREFIX = 'annotation_cache_';

    public static function handle($instance, $method, $args)
    {
        $startTime = microtime(true);
        $refMethod = new ReflectionMethod($instance, $method);
        $attributes = $refMethod->getAttributes(Cacheable::class);

        if (empty($attributes)) {
            return $refMethod->invokeArgs($instance, $args);
        }

        /** @var Cacheable $cacheable */
        $cacheable = $attributes[0]->newInstance();
        // 注册监听器（如果定义了listener）
        if ($cacheable->listener) {
            self::registerListener($cacheable, $refMethod);
        }
        // 构建缓存键
        $cacheKey = self::buildCacheKey($cacheable, $refMethod, $args);

        // 获取缓存实例
        $cache = self::getCacheInstance($cacheable->group);

        // 尝试从缓存获取
        $cached = $cache->get($cacheKey);

        if ($cached !== null) {
            if ($cacheable->collect) {
                self::collectHit($cacheable, $cacheKey, $startTime);
            }
            return $cached;
        }

        // 执行实际方法
        $result = $refMethod->invokeArgs($instance, $args);

        // 检查是否需要跳过缓存
        if (self::shouldSkipCache($cacheable, $result)) {
            if ($cacheable->collect) {
                self::collectMiss($cacheable, $cacheKey, $startTime, 'skip');
            }
            return $result;
        }

        // 计算实际TTL（带随机偏移）
        $ttl = self::calculateTtl($cacheable);

        // 保存到缓存
        if ($ttl > 0) {
            try {
                $cache->set($cacheKey, $result, $ttl);
                // 记录缓存键与监听器的关联
                if ($cacheable->listener) {
                    self::trackCacheKey($cacheable->listener, $cacheKey);
                }
            } catch (\Throwable $e) {
                Log::error("Cache set error: {$e->getMessage()}");
            }
        }

        if ($cacheable->collect) {
            self::collectMiss($cacheable, $cacheKey, $startTime, $ttl > 0 ? 'set' : 'no_ttl');
        }

        return $result;
    }

    private static function buildCacheKey(Cacheable $cacheable, ReflectionMethod $method, array $args): string
    {
        $key = $cacheable->prefix;

        if ($cacheable->value) {
            $params = $method->getParameters();
            $paramValues = [];

            foreach ($params as $index => $param) {
                $paramName = $param->getName();
                $paramValues[$paramName] = $args[$index] ?? null;
            }

            $key .= preg_replace_callback('/#{(.*?)}/',
                function ($matches) use ($paramValues) {
                    $value = $paramValues[$matches[1]] ?? '';
                    return is_scalar($value) ? $value : md5(json_encode($value));
                },
                $cacheable->value
            );
        }

        // 添加统一前缀标识注解缓存
        $fullKey = self::ANNOTATION_PREFIX . $key;
        Log::debug("Built cache key: {$fullKey}");
        return $fullKey;
    }

    private static function getCacheInstance(string $group): \Symfony\Component\Cache\Psr16Cache
    {
        return Cache::store($group);
    }

    private static function shouldSkipCache(Cacheable $cacheable, $result): bool
    {
        // 没有设置跳过条件
        if (empty($cacheable->skipCacheResults)) {
            return false;
        }

        // 检查结果是否在跳过列表中
        foreach ($cacheable->skipCacheResults as $skipValue) {
            if ($result === $skipValue ||
                (is_array($result) && $result === (array)$skipValue)) {
                return true;
            }
        }

        return false;
    }

    private static function calculateTtl(Cacheable $cacheable): int
    {
        $ttl = $cacheable->ttl ?? 0;

        if ($ttl > 0 && $cacheable->offset > 0) {
            // 添加随机偏移量防止缓存雪崩
            $ttl += random_int(0, $cacheable->offset);
        }

        return max(0, $ttl);
    }

    private static function registerListener(Cacheable $cacheable, ReflectionMethod $method): void
    {
        $listenerKey = self::normalizeListenerName($cacheable->listener);
        $methodId = $method->class . '::' . $method->name;

        // 修复：确保监听器名称正确匹配（大小写敏感）
        $listenerKey = strtolower($listenerKey);

        if (!isset(self::$listeners[$listenerKey])) {
            // 添加详细日志
            Log::debug("Registering new listener: $listenerKey for method: $methodId");
            self::$listeners[$listenerKey] = [
                'methods'    => [$methodId],
                'cache_keys' => []
            ];
            // 注册事件监听器（确保只注册一次）
            Log::info("Registering event listener for: $listenerKey",[Event::hasListener($listenerKey),Event::getListeners($listenerKey)]);
            if (!Event::hasListener($listenerKey)) {
                Log::debug("Creating event listener for: $listenerKey");
                // 注册事件监听器
                Event::on($listenerKey, function (...$params) use ($listenerKey) {
                    Log::debug("Event {$listenerKey} received with params: " . json_encode($params));
                    try {
                        Log::debug("Handling event for listener: $listenerKey",[self::$listeners[$listenerKey]]);
                        // 直接调用处理逻辑
                        self::handleCacheEvent($listenerKey, $params);
                    } catch (\Throwable $e) {
                        Log::error("Error in event handler for {$listenerKey}: " . $e->getMessage());
                    }
                });
                // 添加调试日志
                Log::debug("Event listener for {$listenerKey} registered: " .
                    (Event::hasListener($listenerKey) ? 'success' : 'failed'));
            }
        } elseif (!in_array($methodId, self::$listeners[$listenerKey]['methods'])) {
            self::$listeners[$listenerKey]['methods'][] = $methodId;
            Log::debug("Added method {$methodId} to existing listener: {$listenerKey}");
        }
        // 确保事件监听器已注册
       // self::ensureListenerRegistered($listenerKey);
    }

    private static function trackCacheKey(string $listener, string $cacheKey): void
    {
        Log::debug("Tracking cache key: $cacheKey for listener: $listener");
        $listener = self::normalizeListenerName($listener);
        if (isset(self::$listeners[$listener])) {
            // 确保缓存键唯一
            if (!in_array($cacheKey, self::$listeners[$listener]['cache_keys'])) {
                self::$listeners[$listener]['cache_keys'][] = $cacheKey;
                Log::debug("Tracking key '$cacheKey' for listener '$listener'");
            }
        } else {
            Log::warning("Trying to track key for unregistered listener: $listener");
        }
    }

    private static function handleCacheEvent(string $listener, array $params): void
    {
        $listener = self::normalizeListenerName($listener);
        Log::debug("Handling event for listener: $listener",[self::$listeners[$listener]]);
        if (!isset(self::$listeners[$listener])) {
            Log::warning("Received unregistered cache event: {$listener}");
            return;
        }

        $cacheKeys = self::$listeners[$listener]['cache_keys'] ?? [];
        if (empty($cacheKeys)) {
            Log::info("No keys tracked for listener: $listener");
            return;
        }
        Log::debug("Processing keys for listener {$listener}: " . implode(', ', $cacheKeys));

        $deletedCount = 0;
        foreach ($cacheKeys as $cacheKey) {
            try {
                Log::debug("Processing key: {$cacheKey} with params: " . json_encode($params));
                $resolvedKey = self::resolveKeyWithParams($cacheKey, $params);
                Log::debug("Resolved key: $resolvedKey from pattern: $cacheKey");
                if (self::deleteFromAllStores($resolvedKey)) {
                    $deletedCount++;
                    Log::info("Deleted cache key: {$resolvedKey}");
                } else {
                    Log::debug("Key not found: {$resolvedKey}");
                }
            } catch (\Throwable $e) {
                Log::error("Error processing cache event: {$e->getMessage()}");
            }
        }

        Log::info("Cache event handled: {$listener}, deleted {$deletedCount} keys");
    }

    private static function resolveKeyWithParams(string $cacheKey, array $params): string
    {
        // 处理单参数情况
        if (count($params) === 1 && !is_array($params[0])) {
            $paramValue = $params[0];
            return preg_replace_callback('/#{(.*?)}/',
                function ($matches) use ($paramValue) {
                    return (string)$paramValue;
                },
                $cacheKey
            );
        }

        // 处理关联数组参数
        if (count($params) === 1 && is_array($params[0])) {
            $paramArray = $params[0];
            return preg_replace_callback('/#{(.*?)}/',
                function ($matches) use ($paramArray) {
                    $key = $matches[1];
                    return $paramArray[$key] ?? '';
                },
                $cacheKey
            );
        }

        // 默认返回未解析的键
        return $cacheKey;
    }

    private static function deleteFromAllStores(string $key): bool
    {
        $stores = array_keys(config('cache.stores', []));
        $deleted = false;
        Log::debug("Deleting key '$key' from stores: " . implode(', ', $stores));
        foreach ($stores as $store) {
            try {
                $cache = Cache::store($store);

                // 添加调试日志
                if ($cache->has($key)) {
                    Log::debug("Key '$key' found in $store store, deleting...");
                    if ($cache->delete($key)) {
                        Log::info("Deleted cache key '$key' from $store store");
                        $deleted = true;
                    } else {
                        Log::warning("Failed to delete key '$key' from $store store");
                    }
                } else {
                    Log::debug("Key '$key' not found in $store store");
                }
            } catch (\Throwable $e) {
                Log::error("Failed to delete cache from $store: {$e->getMessage()}");
            }
        }

        return $deleted;
    }

    private static function collectHit(Cacheable $cacheable, string $key, float $startTime): void
    {
        $duration = round((microtime(true) - $startTime) * 1000, 2);
        self::$collection[] = [
            'type'     => 'hit',
            'key'      => $key,
            'group'    => $cacheable->group,
            'duration' => $duration,
            'time'     => date('Y-m-d H:i:s')
        ];
    }

    private static function collectMiss(Cacheable $cacheable, string $key, float $startTime, string $reason): void
    {
        $duration = round((microtime(true) - $startTime) * 1000, 2);
        self::$collection[] = [
            'type'     => 'miss',
            'key'      => $key,
            'group'    => $cacheable->group,
            'reason'   => $reason,
            'duration' => $duration,
            'time'     => date('Y-m-d H:i:s')
        ];
    }

    /**
     * 获取收集的缓存统计信息
     */
    public static function getCollection(): array
    {
        return self::$collection;
    }

    /**
     * 清空缓存统计信息
     */
    public static function clearCollection(): void
    {
        self::$collection = [];
    }

    /**
     * 清空所有监听器跟踪信息
     */
    public static function clearListeners(): void
    {
        self::$listeners = [];
        self::$collection = [];
        Log::info("Cleared all cache listeners");
    }

    /**
     * 获取监听器状态
     */
    #[ArrayShape(['listener_count' => "int", 'tracked_keys' => "float|int", 'collection_count' => "int"])]
    public static function getStatus(): array
    {
        return [
            'listener_count' => count(self::$listeners),
            'tracked_keys' => array_sum(array_map('count', array_column(self::$listeners, 'cache_keys'))),
            'collection_count' => count(self::$collection),
        ];
    }

    /**
     * 获取所有监听器信息（用于调试）
     */
    public static function getListeners(): array
    {
        return self::$listeners;
    }

    public static function normalizeListenerName(string $name): string
    {
        return preg_replace('/[^a-z0-9_]/', '_', strtolower($name));
    }

    /**
     * 获取特定监听器跟踪的缓存键
     */
    public static function getTrackedKeys(string $listener): array
    {
        $listener = self::normalizeListenerName($listener);
        return self::$listeners[$listener]['cache_keys'] ?? [];
    }

    /**
     * 强制删除缓存键（绕过事件系统）
     */
    public static function forceDeleteCache(string $pattern, array $params = []): bool
    {
        $cacheKey = self::resolveKeyWithParams($pattern, $params);
        return self::deleteFromAllStores($cacheKey);
    }

    public static function isListenerRegistered(string $listener): bool
    {
        $normalized = self::normalizeListenerName($listener);
        return isset(self::$listeners[$normalized]);
    }

    public static function getListenerInfo(string $listener): array
    {
        $normalized = self::normalizeListenerName($listener);

        if (!isset(self::$listeners[$normalized])) {
            return ['error' => 'Listener not registered'];
        }

        return [
            'name'             => $normalized,
            'methods'          => self::$listeners[$normalized]['methods'] ?? [],
            'cache_keys'       => self::$listeners[$normalized]['cache_keys'] ?? [],
            'event_registered' => Event::hasListener($normalized)
        ];
    }

    private static function ensureListenerRegistered(string $listenerKey): void
    {
        static $registered = [];

        if (isset($registered[$listenerKey])) {
            return;
        }

        // 修复：使用 Webman 兼容的事件注册方式
        if (!Event::hasListener($listenerKey)) {
            $callback = function (...$params) use ($listenerKey) {
                Log::debug("Event callback for {$listenerKey} called");
                self::handleCacheEvent($listenerKey, $params);
            };

            // 使用 Webman 推荐的事件注册方式
            Event::on($listenerKey, $callback);
            $registered[$listenerKey] = true;

            Log::debug("Event listener registered using Event::on: {$listenerKey}");
        }
    }
}
