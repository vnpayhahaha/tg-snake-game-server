<?php

namespace app\repository;

use app\model\ModelTransactionParsingLog;
use DI\Attribute\Inject;
use Illuminate\Database\Eloquent\Builder;


class TransactionParsingLogRepository extends IRepository
{
    #[Inject]
    protected ModelTransactionParsingLog $model;

    public function handleSearch(Builder $query, array $params): Builder
    {

        if (isset($params['raw_data_id']) && filled($params['raw_data_id'])) {
            $query->where('raw_data_id', $params['raw_data_id']);
        }

        if (isset($params['rule_id']) && filled($params['rule_id'])) {
            $query->where('rule_id', $params['rule_id']);
        }

        if (isset($params['variable_name']) && filled($params['variable_name'])) {
            $query->where('variable_name', $params['variable_name']);
        }

        if (isset($params['status']) && filled($params['status'])) {
            $query->where('status', $params['status']);
        }

        if (isset($params['desc']) && filled($params['desc'])) {
            $query->where('desc', $params['desc']);
        }

        return $query;
    }
}
