<?php

namespace app\repository;

use app\model\ModelBankDisbursementBillIdfc;
use DI\Attribute\Inject;
use Illuminate\Database\Eloquent\Builder;

class BankDisbursementBillIdfcRepository extends IRepository
{
    #[Inject]
    protected ModelBankDisbursementBillIdfc $model;

    public function handleSearch(Builder $query, array $params): Builder
    {

        if (isset($params['bill_id']) && filled($params['bill_id'])) {
            $query->where('bill_id', $params['bill_id']);
        }
        if (isset($params['beneficiary_name']) && filled($params['beneficiary_name'])) {
            $query->where('beneficiary_name', $params['beneficiary_name']);
        }

        if (isset($params['beneficiary_account_number']) && filled($params['beneficiary_account_number'])) {
            $query->where('beneficiary_account_number', $params['beneficiary_account_number']);
        }

        if (isset($params['ifsc']) && filled($params['ifsc'])) {
            $query->where('ifsc', $params['ifsc']);
        }

        if (isset($params['transaction_type']) && filled($params['transaction_type'])) {
            $query->where('transaction_type', $params['transaction_type']);
        }

        if (isset($params['debit_account_no']) && filled($params['debit_account_no'])) {
            $query->where('debit_account_no', $params['debit_account_no']);
        }

        if (isset($params['transaction_date']) && filled($params['transaction_date'])) {
            $query->where('transaction_date', $params['transaction_date']);
        }

        if (isset($params['beneficiary_email_id']) && filled($params['beneficiary_email_id'])) {
            $query->where('beneficiary_email_id', $params['beneficiary_email_id']);
        }

        if (isset($params['utr_number']) && filled($params['utr_number'])) {
            $query->where('utr_number', $params['utr_number']);
        }

        if (isset($params['status']) && filled($params['status'])) {
            $query->where('status', $params['status']);
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
