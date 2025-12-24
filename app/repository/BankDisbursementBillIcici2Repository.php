<?php

namespace app\repository;

use app\model\ModelBankDisbursementBillIcici2;
use DI\Attribute\Inject;
use Illuminate\Database\Eloquent\Builder;

class BankDisbursementBillIcici2Repository extends IRepository
{
    #[Inject]
    protected ModelBankDisbursementBillIcici2 $model;

    public function handleSearch(Builder $query, array $params): Builder
    {

        if (isset($params['bill_id']) && filled($params['bill_id'])) {
            $query->where('bill_id', $params['bill_id']);
        }
        if (isset($params['credit_account_number']) && filled($params['credit_account_number'])) {
            $query->where('credit_account_number', $params['credit_account_number']);
        }

        if (isset($params['debit_account_number']) && filled($params['debit_account_number'])) {
            $query->where('debit_account_number', $params['debit_account_number']);
        }

        if (isset($params['ifsc_code']) && filled($params['ifsc_code'])) {
            $query->where('ifsc_code', $params['ifsc_code']);
        }

        if (isset($params['host_reference_number']) && filled($params['host_reference_number'])) {
            $query->where('host_reference_number', $params['host_reference_number']);
        }

        if (isset($params['transaction_status']) && filled($params['transaction_status'])) {
            $query->where('transaction_status', $params['transaction_status']);
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
