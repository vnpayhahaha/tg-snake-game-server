<?php

namespace app\repository;

use app\model\ModelBankDisbursementBillAxis;
use DI\Attribute\Inject;
use Illuminate\Database\Eloquent\Builder;

class BankDisbursementBillAxisRepository extends IRepository
{
    #[Inject]
    protected ModelBankDisbursementBillAxis $model;

    public function handleSearch(Builder $query, array $params): Builder
    {

        if (isset($params['bill_id']) && filled($params['bill_id'])) {
            $query->where('bill_id', $params['bill_id']);
        }
        if (isset($params['payment_method']) && filled($params['payment_method'])) {
            $query->where('payment_method', $params['payment_method']);
        }

        if (isset($params['debit_account_no']) && filled($params['debit_account_no'])) {
            $query->where('debit_account_no', $params['debit_account_no']);
        }

        if (isset($params['beneficiary_account_no']) && filled($params['beneficiary_account_no'])) {
            $query->where('beneficiary_account_no', $params['beneficiary_account_no']);
        }

        if (isset($params['beneficiary_name']) && filled($params['beneficiary_name'])) {
            $query->where('beneficiary_name', $params['beneficiary_name']);
        }

        if (isset($params['payee_name']) && filled($params['payee_name'])) {
            $query->where('payee_name', $params['payee_name']);
        }

        if (isset($params['transaction_status']) && filled($params['transaction_status'])) {
            $query->where('transaction_status', $params['transaction_status']);
        }

        if (isset($params['paid_date']) && filled($params['paid_date'])) {
            $query->where('paid_date', $params['paid_date']);
        }

        if (isset($params['utr_reference_no']) && filled($params['utr_reference_no'])) {
            $query->where('utr_reference_no', $params['utr_reference_no']);
        }

        if (isset($params['reason']) && filled($params['reason'])) {
            $query->where('reason', $params['reason']);
        }

        if (isset($params['remarks']) && filled($params['remarks'])) {
            $query->where('remarks', $params['remarks']);
        }

        if (isset($params['payout_mode']) && filled($params['payout_mode'])) {
            $query->where('payout_mode', $params['payout_mode']);
        }

        if (isset($params['ifsc_code']) && filled($params['ifsc_code'])) {
            $query->where('ifsc_code', $params['ifsc_code']);
        }

        if (isset($params['account_number']) && filled($params['account_number'])) {
            $query->where('account_number', $params['account_number']);
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
