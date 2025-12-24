```php
<?php
// app/service/SettingConfigService.php

namespace app\service;

use app\lib\annotation\Cacheable;
use support\Cache;

class SettingConfigService extends BaseService
{
    #[Cacheable(
        prefix: 'system:config:value',
        value: '_#{key}',
        ttl: 600,
        offset: 60,
        group: 'redis',
        collect: true,
        skipCacheResults: [null, []],
        listener: 'system.config.updated' // 定义监听器名称
    )]
    protected function getConfigByKey(string $key): ?array
    {
        return $this->getMapper()->findByKey($key);
    }
    
    #[Cacheable(
        prefix: 'system:config:all',
        ttl: 86400,
        group: 'file',
        listener: 'system.config.updated' // 共享同一个监听器
    )]
    protected function getAllConfigs(): array
    {
        return $this->getMapper()->fetchAll();
    }
    
    public function updateConfig(string $key, array $config)
    {
        // 更新数据库
        $this->getMapper()->update($key, $config);
        
        // 触发缓存更新事件（自动关联到注解中的listener）
        CacheEventService::trigger('system.config.updated', $key);
    }
    
    public function batchUpdateConfigs(array $configs)
    {
        foreach ($configs as $key => $value) {
            $this->getMapper()->update($key, $value);
        }
        
        // 触发缓存更新事件（不带参数会清除所有关联缓存）
        CacheEventService::trigger('system.config.updated');
    }
    
    // 其他方法...
}
```
1. 在服务方法上使用监听器
```php
#[Cacheable(
    prefix: 'user:profile',
    value: '#{userId}',
    ttl: 3600,
    listener: 'user.profile.updated' // 定义监听器
)]
protected function getUserProfile(int $userId): array
{
    return $this->userRepository->find($userId);
}
```
2. 在更新操作中触发事件
```php
public function updateUserProfile(int $userId, array $profile)
{
    $this->userRepository->update($userId, $profile);
    
    // 触发缓存更新事件
    CacheEventService::trigger('user.profile.updated', $userId);
    
    // 或者直接从方法触发（自动查找注解中的listener）
    CacheEventService::triggerForMethod($this, 'getUserProfile', $userId);
}
```
批量更新时清除所有缓存
```php
public function resetAllConfigs()
{
    $this->getMapper()->resetToDefaults();
    
    // 不带参数触发会清除该监听器关联的所有缓存键
    CacheEventService::trigger('system.config.updated');
}
```

```text
完整生命周期管理
启动时：

服务提供者 EventServiceProvider 注册清理函数

运行时：

当首次调用带 listener 的方法时，自动注册事件监听

缓存键被跟踪记录

事件触发时自动清除相关缓存

停止时：

通过 register_shutdown_function 调用 clearListeners()

释放所有监听器资源

调试时：

使用 cache:listeners 查看当前状态

使用 cache:clear-listeners 手动重置
```
