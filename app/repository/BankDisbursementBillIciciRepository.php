<?php

namespace app\repository;

use app\model\ModelBankDisbursementBillIcici;
use DI\Attribute\Inject;
use Illuminate\Database\Eloquent\Builder;

class BankDisbursementBillIciciRepository extends IRepository
{
    #[Inject]
    protected ModelBankDisbursementBillIcici $model;

    public function handleSearch(Builder $query, array $params): Builder
    {

        if (isset($params['bill_id']) && filled($params['bill_id'])) {
            $query->where('bill_id', $params['bill_id']);
        }
        if (isset($params['pymt_mode']) && filled($params['pymt_mode'])) {
            $query->where('pymt_mode', $params['pymt_mode']);
        }

        if (isset($params['file_sequence_num']) && filled($params['file_sequence_num'])) {
            $query->where('file_sequence_num', $params['file_sequence_num']);
        }

        if (isset($params['debit_acct_no']) && filled($params['debit_acct_no'])) {
            $query->where('debit_acct_no', $params['debit_acct_no']);
        }

        if (isset($params['beneficiary_name']) && filled($params['beneficiary_name'])) {
            $query->where('beneficiary_name', $params['beneficiary_name']);
        }

        if (isset($params['beneficiary_account_no']) && filled($params['beneficiary_account_no'])) {
            $query->where('beneficiary_account_no', $params['beneficiary_account_no']);
        }

        if (isset($params['bene_ifsc_code']) && filled($params['bene_ifsc_code'])) {
            $query->where('bene_ifsc_code', $params['bene_ifsc_code']);
        }

        if (isset($params['remark']) && filled($params['remark'])) {
            $query->where('remark', $params['remark']);
        }

        if (isset($params['pymt_date']) && filled($params['pymt_date'])) {
            $query->where('pymt_date', $params['pymt_date']);
        }

        if (isset($params['status']) && filled($params['status'])) {
            $query->where('status', $params['status']);
        }

        if (isset($params['customer_ref_no']) && filled($params['customer_ref_no'])) {
            $query->where('customer_ref_no', $params['customer_ref_no']);
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
