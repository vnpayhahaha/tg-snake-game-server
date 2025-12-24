<?php

namespace app\repository;

use app\model\ModelBankDisbursementBillAxisNeft;
use DI\Attribute\Inject;
use Illuminate\Database\Eloquent\Builder;

class BankDisbursementBillAxisNeftRepository extends IRepository
{
    #[Inject]
    protected ModelBankDisbursementBillAxisNeft $model;

    public function handleSearch(Builder $query, array $params): Builder
    {

        if (isset($params['bill_id']) && filled($params['bill_id'])) {
            $query->where('bill_id', $params['bill_id']);
        }
        if (isset($params['receipient_name']) && filled($params['receipient_name'])) {
            $query->where('receipient_name', $params['receipient_name']);
        }

        if (isset($params['account_number']) && filled($params['account_number'])) {
            $query->where('account_number', $params['account_number']);
        }

        if (isset($params['ifsc_code']) && filled($params['ifsc_code'])) {
            $query->where('ifsc_code', $params['ifsc_code']);
        }

        if (isset($params['status']) && filled($params['status'])) {
            $query->where('status', $params['status']);
        }

        if (isset($params['failure_reason']) && filled($params['failure_reason'])) {
            $query->where('failure_reason', $params['failure_reason']);
        }
        if (isset($params['created_by']) && filled($params['created_by'])) {
            $query->where('created_by', $params['created_by']);
        }
        if (isset($params['upload_id']) && filled($params['upload_id'])) {
            $query->where('upload_id', $params['upload_id']);
        }
        if (isset($params['order_no']) && filled($params['order_no'])) {
            $query->where('order_no', $params['order_no']);
        }
        if (isset($params['file_hash']) && filled($params['file_hash'])) {
            $query->where('file_hash', $params['file_hash']);
        }

        // 创建时间
        if (isset($params['created_at']) && is_array($params['created_at']) && count($params['created_at']) === 2) {
            $query->whereBetween(
                'created_at',
                [
                    $params['created_at'][0],
                    $params['created_at'][1]
                ]
            );
        }
        return $query;
    }
}
