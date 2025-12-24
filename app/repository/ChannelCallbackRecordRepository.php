<?php

namespace app\repository;

use app\model\ModelChannelCallbackRecord;
use DI\Attribute\Inject;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class ChannelCallbackRecordRepository.
 * @extends IRepository<ModelChannelCallbackRecord>
 */
final class ChannelCallbackRecordRepository extends IRepository
{
    #[Inject]
    protected ModelChannelCallbackRecord $model;

    public function handleSearch(Builder $query, array $params): Builder
    {

        if (isset($params['callback_id']) && filled($params['callback_id'])) {
            $query->where('callback_id', $params['callback_id']);
        }

        if (isset($params['channel_id']) && filled($params['channel_id'])) {
            $query->where('channel_id', $params['channel_id']);
        }

        if (isset($params['original_request_id']) && filled($params['original_request_id'])) {
            $query->where('original_request_id', $params['original_request_id']);
        }

        if (isset($params['callback_type']) && filled($params['callback_type'])) {
            $query->where('callback_type', $params['callback_type']);
        }

        if (isset($params['callback_body']) && filled($params['callback_body'])) {
            $query->where('callback_body', $params['callback_body']);
        }

        if (isset($params['status']) && filled($params['status'])) {
            $query->where('status', $params['status']);
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
