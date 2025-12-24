<?php

namespace app\repository;

use app\model\ModelBankDisbursementBillYesmsme;
use DI\Attribute\Inject;
use Illuminate\Database\Eloquent\Builder;

class BankDisbursementBillYesmsmeRepository extends IRepository
{
    #[Inject]
    protected ModelBankDisbursementBillYesmsme $model;

    public function handleSearch(Builder $query, array $params): Builder
    {

        if (isset($params['bill_id']) && filled($params['bill_id'])) {
            $query->where('bill_id', $params['bill_id']);
        }
        if (isset($params['record_ref_no']) && filled($params['record_ref_no'])) {
            $query->where('record_ref_no', $params['record_ref_no']);
        }

        if (isset($params['file_ref_no']) && filled($params['file_ref_no'])) {
            $query->where('file_ref_no', $params['file_ref_no']);
        }

        if (isset($params['ebanking_ref_no']) && filled($params['ebanking_ref_no'])) {
            $query->where('ebanking_ref_no', $params['ebanking_ref_no']);
        }

        if (isset($params['contract_ref_no']) && filled($params['contract_ref_no'])) {
            $query->where('contract_ref_no', $params['contract_ref_no']);
        }

        if (isset($params['record_status']) && filled($params['record_status'])) {
            $query->where('record_status', $params['record_status']);
        }

        if (isset($params['status_code']) && filled($params['status_code'])) {
            $query->where('status_code', $params['status_code']);
        }

        if (isset($params['status_description']) && filled($params['status_description'])) {
            $query->where('status_description', $params['status_description']);
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
