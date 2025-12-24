<?php
// app/lib/annotation/Cacheable.php

namespace app\lib\annotation;

use Attribute;
use Webman\Event\Event;

#[Attribute(Attribute::TARGET_METHOD)]
final class Cacheable
{
    /**
     * @param string|null $prefix 缓存键前缀
     * @param string|null $value 缓存键格式（支持 #{参数名} 语法）
     * @param int|null $ttl 缓存时间（秒）
     * @param string|null $listener 缓存更新事件名称
     * @param int $offset TTL随机偏移量（防止缓存雪崩）
     * @param string $group 缓存存储组（对应 config/cache.php 配置）
     * @param bool $collect 是否收集缓存统计信息
     * @param array|null $skipCacheResults 跳过缓存的结果值
     */
    public function __construct(
        public ?string $prefix = null,
        public ?string $value = null,
        public ?int $ttl = null,
        public ?string $listener = null,
        public int $offset = 0,
        public string $group = '',
        public bool $collect = false,
        public ?array $skipCacheResults = null
    ) {
    }

    /**
     * 触发缓存更新事件
     */
    public function triggerEvent(array $params): void
    {
        if ($this->listener) {
            Event::dispatch($this->listener, $params);
        }
    }
}
