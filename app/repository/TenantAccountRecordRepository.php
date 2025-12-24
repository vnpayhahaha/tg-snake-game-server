<?php

namespace app\repository;

use app\model\ModelTenantAccountRecord;
use DI\Attribute\Inject;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class TenantAccountRecordRepository.
 * @extends IRepository<ModelTenantAccountRecord>
 */
class TenantAccountRecordRepository extends IRepository
{

    #[Inject]
    protected ModelTenantAccountRecord $model;

    public function handleSearch(Builder $query, array $params): Builder
    {

        if (isset($params['tenant_id']) && filled($params['tenant_id'])) {
            $query->where('tenant_id', $params['tenant_id']);
        }

        if (isset($params['account_type']) && filled($params['account_type'])) {
            $query->where('account_type', $params['account_type']);
        }
        if (isset($params['account_id']) && filled($params['account_id'])) {
            $query->where('account_id', $params['account_id']);
        }

        if (isset($params['change_type']) && filled($params['change_type'])) {
            if (!is_array($params['change_type'])) {
                $query->where('change_type', $params['change_type']);
            } else {
                $query->whereRaw('FIND_IN_SET(?,change_type)', $params['change_type']);
            }
        }

        if (isset($params['transaction_no']) && filled($params['transaction_no'])) {
            $query->where('transaction_no', $params['transaction_no']);
        }

        if (isset($params['created_at']) && filled($params['created_at']) && is_array($params['created_at']) && count($params['created_at']) == 2) {
            $query->whereBetween(
                'created_at',
                [$params['created_at'][0], $params['created_at'][1]]
            );
        }

        return $query;
    }

    public function page(array $params = [], ?int $page = null, ?int $pageSize = null): array
    {
        $result = $this->perQuery($this->getQuery(), $params)->with('tenant:tenant_id,company_name')->paginate(
            perPage: $pageSize,
            pageName: static::PER_PAGE_PARAM_NAME,
            page: $page,
        );
        return $this->handlePage($result);
    }
}
