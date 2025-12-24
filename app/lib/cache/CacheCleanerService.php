<?php

namespace app\lib\cache;

use Illuminate\Support\Facades\DB;
use support\Cache;
use support\Log;
use support\Redis;

class CacheCleanerService
{
    // 清除所有存储组的注解缓存
    public static function clearAllAnnotationCache(): int
    {
        $stores = array_keys(config('cache.stores', []));
        $count = 0;
        Log::info("Starting to clear annotation cache for stores: " . implode(', ', $stores));

        foreach ($stores as $store) {
            try {
                $cleaned = self::clearStoreAnnotationCache($store);
                $count += $cleaned;
                Log::info("Cleared {$cleaned} items from store: {$store}");
            } catch (\Throwable $e) {
                Log::error("Failed to clear cache in store {$store}: " . $e->getMessage());
            }
        }

        Log::info("Cleared {$count} annotation cache items on startup");
        return $count;
    }

    // 清除指定存储组的注解缓存
    public static function clearStoreAnnotationCache(string $store): int
    {
        $prefix = \app\process\CacheableProcessor::ANNOTATION_PREFIX;
        $count = 0;
        // 根据存储类型选择清理策略
        $driver = config("cache.stores.{$store}.driver", 'file');
        Log::debug("Clearing cache for store: {$store} (driver: {$driver})");
        switch ($driver) {
            case 'redis':
                $count = self::clearRedisStore($store, $prefix);
                break;

            case 'file':
                $count = self::clearFileStore($store, $prefix);
                break;

            case 'database':
                $count = self::clearDatabaseStore($store, $prefix);
                break;

            case 'memcached':
                $count = self::clearMemcachedStore($store, $prefix);
                break;

            default:
                Log::warning("Unsupported cache driver: {$driver} for store: {$store}");
        }

        return $count;
    }
    // Redis 存储清理
    private static function clearRedisStore(string $store, string $prefix): int
    {
        $count = 0;
        $connectionName = config("cache.stores.{$store}.connection", 'default');

        try {
            $redis = Redis::connection($connectionName);
            $pattern = $prefix . '*';

            Log::debug("Clearing Redis cache for pattern: {$pattern}");

            // 更高效的删除方式
            $keys = $redis->keys($pattern);

            if (!empty($keys)) {
                // 分批删除避免阻塞
                foreach (array_chunk($keys, 1000) as $chunk) {
                    $redis->del($chunk);
                    $count += count($chunk);
                }
                Log::info("Deleted {$count} keys from Redis store: {$store}");
            } else {
                Log::debug("No keys found for pattern: {$pattern}");
            }
        } catch (\Throwable $e) {
            Log::error("Redis clear error: " . $e->getMessage());
        }

        return $count;
    }

    // 文件存储清理
    private static function clearFileStore(string $store, string $prefix): int
    {
        $count = 0;
        $path = config("cache.stores.{$store}.path", runtime_path('cache'));

        Log::debug("Clearing file cache in directory: {$path}");

        if (!is_dir($path)) {
            Log::warning("Cache directory not found: {$path}");
            // 如果目录不存在，则创建
            mkdir($path, 0755, true);
            return 0;
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && strpos($file->getFilename(), $prefix) === 0) {
                $filePath = $file->getRealPath();
                if (unlink($filePath)) {
                    $count++;
                } else {
                    Log::warning("Failed to delete file: {$filePath}");
                }
            }
        }

        Log::info("Deleted {$count} files from file store: {$store}");
        return $count;
    }

    // 数据库存储清理（兼容 Webman 数据库组件）
    private static function clearDatabaseStore(string $store, string $prefix): int
    {
        $table = config("cache.stores.{$store}.table", 'cache');
        $connectionName = config("cache.stores.{$store}.connection", 'default');

        Log::debug("Clearing database cache in table: {$table}");

        try {
            // 使用 Webman 的数据库组件
            $count = DB::connection($connectionName)
                ->table($table)
                ->where('key', 'like', $prefix . '%')
                ->count();

            if ($count > 0) {
                DB::connection($connectionName)
                    ->table($table)
                    ->where('key', 'like', $prefix . '%')
                    ->delete();

                Log::info("Deleted {$count} records from database store: {$store}");
            } else {
                Log::debug("No records found for prefix: {$prefix}");
            }

            return $count;
        } catch (\Throwable $e) {
            Log::error("Database clear error: " . $e->getMessage());
            return 0;
        }
    }

    // Memcached 存储清理
    private static function clearMemcachedStore(string $store, string $prefix): int
    {
        $count = 0;

        try {
            $cache = Cache::store($store);
            $memcached = $cache->getMemcached();

            if (!method_exists($memcached, 'getAllKeys')) {
                Log::warning("Memcached getAllKeys method not available");
                return 0;
            }

            $keys = $memcached->getAllKeys();
            if (empty($keys)) {
                Log::debug("No keys found in Memcached store: {$store}");
                return 0;
            }

            foreach ($keys as $key) {
                if (strpos($key, $prefix) === 0) {
                    if ($memcached->delete($key)) {
                        $count++;
                    }
                }
            }

            Log::info("Deleted {$count} keys from Memcached store: {$store}");
            return $count;
        } catch (\Throwable $e) {
            Log::error("Memcached clear error: " . $e->getMessage());
            return 0;
        }
    }
    // 清除特定组的注解缓存
    public static function clearGroupAnnotationCache(string $group): int
    {
        return self::clearStoreAnnotationCache($group);
    }
    // 清理过期的缓存键跟踪（可选）
    public static function cleanupExpiredTrackedKeys(int $expireDays = 7): int
    {
        // 这里可以添加清理过期跟踪键的逻辑
        return 0;
    }

    // 添加清除所有缓存的命令
    public static function clearAllCache(): int
    {
        $stores = array_keys(config('cache.stores', []));
        $totalCount = 0;

        foreach ($stores as $store) {
            try {
                Cache::store($store)->clear();
                Log::info("Cleared entire cache for store: {$store}");
                $totalCount++;
            } catch (\Throwable $e) {
                Log::error("Failed to clear cache in store {$store}: " . $e->getMessage());
            }
        }

        return $totalCount;
    }
}
