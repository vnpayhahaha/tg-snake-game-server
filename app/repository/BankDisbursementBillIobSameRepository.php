<?php

namespace app\repository;

use app\model\ModelBankDisbursementBillIobSame;
use DI\Attribute\Inject;
use Illuminate\Database\Eloquent\Builder;

class BankDisbursementBillIobSameRepository extends IRepository
{
    #[Inject]
    protected ModelBankDisbursementBillIobSame $model;

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

        if (isset($params['type']) && filled($params['type'])) {
            $query->where('type', $params['type']);
        }

        if (isset($params['number']) && filled($params['number'])) {
            $query->where('number', $params['number']);
        }

        if (isset($params['status']) && filled($params['status'])) {
            $query->where('status', $params['status']);
        }

        if (isset($params['remarks']) && filled($params['remarks'])) {
            $query->where('remarks', $params['remarks']);
        }

        if (isset($params['narration']) && filled($params['narration'])) {
            $query->where('narration', $params['narration']);
        }

        if (isset($params['utr_no']) && filled($params['utr_no'])) {
            $query->where('utr_no', $params['utr_no']);
        }

        if (isset($params['reason']) && filled($params['reason'])) {
            $query->where('reason', $params['reason']);
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
