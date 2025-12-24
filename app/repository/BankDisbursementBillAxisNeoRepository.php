<?php

namespace app\repository;

use app\model\ModelBankDisbursementBillAxisNeo;
use DI\Attribute\Inject;
use Illuminate\Database\Eloquent\Builder;

class BankDisbursementBillAxisNeoRepository extends IRepository
{
    #[Inject]
    protected ModelBankDisbursementBillAxisNeo $model;

    public function handleSearch(Builder $query, array $params): Builder
    {

        if (isset($params['bill_id']) && filled($params['bill_id'])) {
            $query->where('bill_id', $params['bill_id']);
        }
        if (isset($params['srl_no']) && filled($params['srl_no'])) {
            $query->where('srl_no', $params['srl_no']);
        }

        if (isset($params['tran_date']) && filled($params['tran_date'])) {
            $query->where('tran_date', $params['tran_date']);
        }

        if (isset($params['chq_no']) && filled($params['chq_no'])) {
            $query->where('chq_no', $params['chq_no']);
        }

        if (isset($params['dr_cr']) && filled($params['dr_cr'])) {
            $query->where('dr_cr', $params['dr_cr']);
        }

        if (isset($params['created_at']) && filled($params['created_at'])) {
            $query->where('created_at', $params['created_at']);
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
