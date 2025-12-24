<?php

namespace app\repository;

use app\model\ModelChannel;
use DI\Attribute\Inject;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class ChannelRepository.
 * @extends IRepository<ModelChannel>
 */
final class ChannelRepository extends IRepository
{
    #[Inject]
    protected ModelChannel $model;

    public function handleSearch(Builder $query, array $params): Builder
    {
        if (isset($params['id']) && filled($params['id'])) {
            $query->where('id', '=', $params['id']);
        }

        if (isset($params['channel_code']) && filled($params['channel_code'])) {
            $query->where('channel_code', 'like', '%' . $params['channel_code'] . '%');
        }

        if (isset($params['channel_name']) && filled($params['channel_name'])) {
            $query->where('channel_name', 'like', '%' . $params['channel_name'] . '%');
        }

        if (isset($params['channel_type']) && filled($params['channel_type'])) {
            $query->where('channel_type', $params['channel_type']);
        }

        if (isset($params['country_code']) && filled($params['country_code'])) {
            $query->where('country_code', $params['country_code']);
        }

        if (isset($params['currency']) && filled($params['currency'])) {
            $query->where('currency', $params['currency']);
        }

        if (isset($params['support_collection']) && filled($params['support_collection'])) {
            $query->where('support_collection', $params['support_collection']);
        }

        if (isset($params['support_disbursement']) && filled($params['support_disbursement'])) {
            $query->where('support_disbursement', $params['support_disbursement']);
        }

        // 状态 (1正常 2停用)
        if (isset($params['status']) && filled($params['status'])) {
            $query->where('status', '=', $params['status']);
        }
        // 创建时间
        if (isset($params['created_at']) && filled($params['created_at']) && is_array($params['created_at']) && count($params['created_at']) == 2) {
            $query->whereBetween(
                'created_at',
                [ $params['created_at'][0], $params['created_at'][1] ]
            );
        }

        // 更新时间
        if (isset($params['updated_at']) && filled($params['updated_at']) && is_array($params['updated_at']) && count($params['updated_at']) == 2) {
            $query->whereBetween(
                'updated_at',
                [ $params['updated_at'][0], $params['updated_at'][1] ]
            );
        }

        return $query;
    }
}
