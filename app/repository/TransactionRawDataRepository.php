<?php

namespace app\repository;

use app\model\ModelTransactionRawData;
use DI\Attribute\Inject;
use Illuminate\Database\Eloquent\Builder;


class TransactionRawDataRepository extends IRepository
{
    #[Inject]
    protected ModelTransactionRawData $model;

    public function handleSearch(Builder $query, array $params): Builder
    {

        if (isset($params['content']) && filled($params['content'])) {
            $query->where('content', $params['content']);
        }

        if (isset($params['source']) && filled($params['source'])) {
            $query->where('source', $params['source']);
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
            ->with('bank_account:id,branch_name,account_number,account_holder,bank_code')
            ->with('transaction_parsing_log')
            ->paginate(
                perPage: $pageSize,
                pageName: static::PER_PAGE_PARAM_NAME,
                page: $page,
            );
        return $this->handlePage($result);
    }
}
