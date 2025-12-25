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

    // 获取当前管理员用户名（兼容多种环境）
    public function getCurrentUserName(): string
    {
        try {
            $request = request();
            if ($request && isset($request->user) && isset($request->user->username)) {
                return $request->user->username;
            }
        } catch (\Throwable $e) {
            // 忽略错误，返回默认值
        }
        return '';
    }

    public function getCurrentUserId(): int
    {
        try {
            $request = request();
            if ($request && isset($request->user) && isset($request->user->id)) {
                return $request->user->id;
            }
        } catch (\Throwable $e) {
            // 忽略错误，返回默认值
        }
        return 0;
    }
}
