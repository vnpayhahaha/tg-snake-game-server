<?php

namespace app\repository;

use app\model\ModelBankDisbursementBillIobOther;
use DI\Attribute\Inject;
use Illuminate\Database\Eloquent\Builder;

class BankDisbursementBillIobOtherRepository extends IRepository
{
    #[Inject]
    protected ModelBankDisbursementBillIobOther $model;

    public function handleSearch(Builder $query, array $params): Builder
    {

        if (isset($params['bill_id']) && filled($params['bill_id'])) {
            $query->where('bill_id', $params['bill_id']);
        }
        if (isset($params['s_no']) && filled($params['s_no'])) {
            $query->where('s_no', $params['s_no']);
        }

        if (isset($params['name']) && filled($params['name'])) {
            $query->where('name', $params['name']);
        }

        if (isset($params['ifsc_code']) && filled($params['ifsc_code'])) {
            $query->where('ifsc_code', $params['ifsc_code']);
        }

        if (isset($params['number']) && filled($params['number'])) {
            $query->where('number', $params['number']);
        }

        if (isset($params['status']) && filled($params['status'])) {
            $query->where('status', $params['status']);
        }

        if (isset($params['utr_no']) && filled($params['utr_no'])) {
            $query->where('utr_no', $params['utr_no']);
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
