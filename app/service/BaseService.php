<?php

namespace app\service;

use app\process\CacheableProcessor;

abstract class BaseService extends IService
{
    public function __call($method, $args)
    {
        echo "[BaseService] 拦截到方法调用: {$method}\n";

        // 检查方法是否存在
        if (!method_exists($this, $method)) {
            echo "[BaseService] 方法 {$method} 不存在\n";
            throw new \BadMethodCallException("Method {$method} does not exist");
        }

        echo "[BaseService] 转发到CacheableProcessor处理\n";
        return CacheableProcessor::handle($this, $method, $args);
    }

    /**
     * 获取缓存统计信息（仅在collect=true时有效）
     */
    public static function getCacheStats(): array
    {
        return CacheableProcessor::getCollection();
    }

    /**
     * 清空缓存统计信息
     */
    public static function clearCacheStats()
    {
        CacheableProcessor::clearCollection();
    }

    // 获取当前管理员用户名
    public function getCurrentUserName(): string
    {
        return request()->user->username ?? '';
    }

    public function getCurrentUserId(): int
    {
        return request()->user->id ?? 0;
    }
}
