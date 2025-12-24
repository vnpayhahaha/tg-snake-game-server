<?php

namespace app\repository;

use app\model\ModelSystemConfig;
use DI\Attribute\Inject;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class SettingConfigRepository.
 * @extends IRepository<ModelSystemConfig>
 */
final class SystemConfigRepository extends IRepository
{
    #[Inject]
    protected ModelSystemConfig $model;

    /**
     * 搜索处理器.
     */
    public function handleSearch(Builder $query, array $params): Builder
    {
        if (isset($params['group_id']) && filled($params['group_id'])) {
            $query->where('group_id', '=', $params['group_id']);
        }

        if (isset($params['key']) && filled($params['key'])) {
            $query->where('key', '=', $params['key']);
        }

        if (isset($params['value']) && filled($params['value'])) {
            $query->where('value', '=', $params['value']);
        }

        if (isset($params['name']) && filled($params['name'])) {
            $query->where('name', '=', $params['name']);
        }

        if (isset($params['input_type']) && filled($params['input_type'])) {
            $query->where('input_type', '=', $params['input_type']);
        }

        if (isset($params['config_select_data']) && filled($params['config_select_data'])) {
            $query->where('config_select_data', '=', $params['config_select_data']);
        }

        if (isset($params['sort']) && filled($params['sort'])) {
            $query->where('sort', '=', $params['sort']);
        }

        if (isset($params['remark']) && filled($params['remark'])) {
            $query->where('remark', '=', $params['remark']);
        }

        return $query;
    }

    /**
     * 按Key获取配置.
     */
    public function getConfigByKey(string $key): array
    {
        $model = $this->model::query()->select([
            'group_id', 'name', 'key', 'value', 'sort', 'input_type', 'config_select_data',
        ])->where('key', '=', $key)->first();
        return $model ? $model->toArray() : [];
    }

}
