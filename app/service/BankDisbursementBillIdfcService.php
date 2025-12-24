<?php

namespace app\service;

use app\constants\DisbursementOrderVerificationQueue;
use app\model\ModelBankDisbursementUpload;
use app\repository\BankDisbursementBillIdfcRepository;
use app\service\handle\BankDisbursementBillAbstract;
use DI\Attribute\Inject;

class BankDisbursementBillIdfcService extends BankDisbursementBillAbstract
{
    #[Inject]
    public BankDisbursementBillIdfcRepository $repository;

    protected array $FieldMap = [
        'beneficiaryname'          => 'beneficiary_name',
        'beneficiaryaccountnumber' => 'beneficiary_account_number',
        'ifsc'                     => 'ifsc',
        'transactiontype'          => 'transaction_type',
        'debitaccountno'           => 'debit_account_no',
        'transactiondate'          => 'transaction_date',
        'amount'                   => 'amount',
        'currency'                 => 'currency',
        'beneficiaryemailid'       => 'beneficiary_email_id',
        'remarks'                  => 'remarks',
        'utrnumber'                => 'utr_number',
        'status'                   => 'status',
        'errors'                   => 'errors',
    ];

    public function importBill(ModelBankDisbursementUpload $model): bool
    {
        try {
            $this->parseData($model->id, $model->path, function ($data) use ($model) {
                if (!isset($data['debit_account_no'], $data['amount'], $data['status'], $data['remarks'])) {
                    $model->increment('failure_count');
                    return false;
                }
                $data['file_hash'] = $model->hash;
                $data['amount'] = str_replace(',', '', $data['amount']);

                $data['order_no'] = $data['remarks'];
                $data['created_at'] = date('Y-m-d H:i:s');
                $data['created_by'] = $model->created_by;
                $data['upload_id'] = $model->id;
                $bill_data = $this->repository->create($data);
                if ($bill_data) {
                    // 判断支付状态
                    $statusValue = strtoupper(trim($data['status']));
                    switch ($statusValue) {
                        case 'SUCCESS':
                            $model->increment('success_count');
                            $payment_status = DisbursementOrderVerificationQueue::PAY_STATUS_SUCCESS;
                            break;
                        default:
                            $model->increment('failure_count');
                            $payment_status = DisbursementOrderVerificationQueue::PAY_STATUS_FAIL;
                            break;
                    }
                    return [
                        'order_no'         => $data['order_no'],
                        'amount'           => $data['amount'],
                        'utr'              => $data['utr_number'] ?? '',
                        'payment_status'   => $payment_status,
                        'rejection_reason' => $data['errors'] ?? '',
                    ];

                }
                return false;
            });

        } catch (\Throwable $e) {
            var_dump('导入idfc账单异常错误：', $e->getMessage());
            throw $e;
        }
        return false;
    }
}