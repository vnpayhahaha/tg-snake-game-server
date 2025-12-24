<?php

namespace app\repository;

use app\model\ModelBankDisbursementBillBandhan;
use DI\Attribute\Inject;
use Illuminate\Database\Eloquent\Builder;

class BankDisbursementBillBandhanRepository extends IRepository
{
    #[Inject]
    protected ModelBankDisbursementBillBandhan $model;

    public function handleSearch(Builder $query, array $params): Builder
    {

        if (isset($params['bill_id']) && filled($params['bill_id'])) {
            $query->where('bill_id', $params['bill_id']);
        }
        if (isset($params['core_ref_number']) && filled($params['core_ref_number'])) {
            $query->where('core_ref_number', $params['core_ref_number']);
        }

        if (isset($params['status']) && filled($params['status'])) {
            $query->where('status', $params['status']);
        }

        if (isset($params['payment_date']) && filled($params['payment_date'])) {
            $query->where('payment_date', $params['payment_date']);
        }

        if (isset($params['payment_type']) && filled($params['payment_type'])) {
            $query->where('payment_type', $params['payment_type']);
        }

        if (isset($params['source_account_number']) && filled($params['source_account_number'])) {
            $query->where('source_account_number', $params['source_account_number']);
        }

        if (isset($params['destination_account_number']) && filled($params['destination_account_number'])) {
            $query->where('destination_account_number', $params['destination_account_number']);
        }

        if (isset($params['beneficiary_name']) && filled($params['beneficiary_name'])) {
            $query->where('beneficiary_name', $params['beneficiary_name']);
        }

        if (isset($params['beneficiary_code']) && filled($params['beneficiary_code'])) {
            $query->where('beneficiary_code', $params['beneficiary_code']);
        }

        if (isset($params['beneficiary_account_type']) && filled($params['beneficiary_account_type'])) {
            $query->where('beneficiary_account_type', $params['beneficiary_account_type']);
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
