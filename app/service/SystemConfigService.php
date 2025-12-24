<?php

namespace app\service;

use app\lib\annotation\Cacheable;
use app\lib\cache\CacheEventService;
use app\repository\SystemConfigRepository;
use Illuminate\Database\Eloquent\Collection;
use support\Cache;
use Webman\Event\Event;

/**
 * @extends IService<SystemConfigRepository>
 */
class SystemConfigService extends BaseService
{
    protected SystemConfigRepository $repository;

    public function __construct(SystemConfigRepository $repository)
    {
        $this->repository = $repository;
    }

    // 查询数据
    public function getDetails($params): Collection
    {
        // 获取查询构建器
        $query = $this->repository->getQuery();
        $query->where($params);
        $query->orderBy('created_at', 'desc');
        return $query->get();  // 执行查询并返回结果
    }

    // 根据key删除数据
    public function deleteByKey($data): bool
    {
        // 获取传递进来的 key
        $key = $data['key'] ?? null; // 使用 ?? 来确保如果没有 key 字段，$key 为 null

        if ($key) {
            $deleted = $this->repository->getModel()->where('key', $key)->delete();
            return $deleted > 0;
        }
        return false; // 如果没有 key 字段，返回 false
    }

    /**
     * 写入数据，使用 updateOrCreate 处理.
     */
    public function upsertData(array $params): void
    {
        var_dump('$params te===',$params);
        $model = $this->repository->getModel();
        foreach ($params as $param) {
            // 仅处理checkbox类型的value字段
            if ($param['input_type'] === 'checkbox' && \is_array($param['value'])) {
                $param['value'] = implode(',', $param['value']);
            }
            var_dump('$param te===',$param);
            // 执行更新或插入操作
//            $model->getModel()->updateOrCreate(
//                [
//                    'group_id' => $params['group_id'],
//                    'key'      => $params['key'],
//                ],
//                $params
//            );
            $find = $model->where('key', $param['key'])->first();
            if ($find && $find->update($param)) {
                var_dump('updad te===');
                // 触发缓存更新事件（自动关联到注解中的listener）
                CacheEventService::trigger('system.config.updated', $param['key']);
            } else {
                $model->create($param);
            }
        }
    }
    public function updateData(array $param): void
    {

        $model = $this->repository->getModel();
        // 仅处理checkbox类型的value字段
        if ($param['input_type'] === 'checkbox' && \is_array($param['value'])) {
            $param['value'] = implode(',', $param['value']);
        }
        // 执行更新或插入操作
//            $model->getModel()->updateOrCreate(
//                [
//                    'group_id' => $params['group_id'],
//                    'key'      => $params['key'],
//                ],
//                $params
//            );
        $find = $model->where('key', $param['key'])->first();
        if ($find && $find->update($param)) {

            // 触发缓存更新事件（自动关联到注解中的listener）
           CacheEventService::trigger('system_config_update',['key' => $param['key']]);
        } else {
            $model->create($param);
        }
    }
    private function clearConfigCache(string $key)
    {
        // 清除单条配置缓存
        $cacheKey = 'system:config:value:_' . $key;
        Cache::store('redis')->delete($cacheKey);

        // 清除全部配置缓存
        Cache::store('file')->delete('system:config:all');

        // 触发监听事件
        if ($this->getCacheListener()) {
            Event::dispatch($this->getCacheListener(), [$key]);
        }
    }

    private function getCacheListener(): ?string
    {
        // 从注解获取监听器名称
        $refMethod = new \ReflectionMethod($this, 'getConfigByKey');
        $attributes = $refMethod->getAttributes(Cacheable::class);

        if (!empty($attributes)) {
            $cacheable = $attributes[0]->newInstance();
            return $cacheable->listener;
        }

        return null;
    }

    #[Cacheable(
        prefix: 'system:config:value',
        value: '_#{key}',
        ttl: 600,
        listener: 'system-config-update',
        group: 'redis'
    )]
    protected function getConfigByKey(string $key): ?array
    {
        echo "[ConfigService] 执行实际数据库查询，key: {$key}\n";
        return $this->repository->getConfigByKey($key);
    }

}
