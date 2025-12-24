<?php

namespace app\repository;

use app\model\ModelChannelRequestRecord;
use DI\Attribute\Inject;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class ChannelRequestRecordRepository.
 * @extends IRepository<ModelChannelRequestRecord>
 */
final class ChannelRequestRecordRepository extends IRepository
{
    #[Inject]
    protected ModelChannelRequestRecord $model;

    public function handleSearch(Builder $query, array $params): Builder
    {

        if (isset($params['request_id']) && filled($params['request_id'])) {
            $query->where('request_id', $params['request_id']);
        }

        if (isset($params['channel_id']) && filled($params['channel_id'])) {
            $query->where('channel_id', $params['channel_id']);
        }

        if (isset($params['api_method']) && filled($params['api_method'])) {
            $query->where('api_method', $params['api_method']);
        }

        if (isset($params['request_params']) && filled($params['request_params'])) {
            $query->where('request_params', $params['request_params']);
        }

        if (isset($params['request_body']) && filled($params['request_body'])) {
            $query->where('request_body', $params['request_body']);
        }

        if (isset($params['http_status_code']) && filled($params['http_status_code'])) {
            $query->where('http_status_code', $params['http_status_code']);
        }

        return $query;
    }

    public function page(array $params = [], ?int $page = null, ?int $pageSize = null): array
    {
        $result = $this->perQuery($this->getQuery(), $params)
            ->with('channel:id,channel_name,channel_code,channel_icon')
            ->paginate(
                perPage: $pageSize,
                pageName: static::PER_PAGE_PARAM_NAME,
                page: $page,
            );
        return $this->handlePage($result);
    }
}
