<?php

namespace app\service;

use app\constants\DisbursementOrderVerificationQueue;
use app\model\ModelBankDisbursementUpload;
use app\repository\BankDisbursementBillIcici2Repository;
use app\service\handle\BankDisbursementBillAbstract;
use DI\Attribute\Inject;

class BankDisbursementBillIcici2Service extends BankDisbursementBillAbstract
{
    #[Inject]
    public BankDisbursementBillIcici2Repository $repository;

    protected array $FieldMap = [
        'networkid'                => 'network_id',
        'creditaccountnumber'      => 'credit_account_number',
        'debitaccountnumber'       => 'debit_account_number',
        'ifsccode'                 => 'ifsc_code',
        'totalamount'              => 'total_amount',
        'hostreferencenumber'      => 'host_reference_number',
        'transactionremarks'       => 'transaction_remarks',
        'transactionstatus'        => 'transaction_status',
        'transactionstatusremarks' => 'transaction_status_remarks',
    ];

    public function importBill(ModelBankDisbursementUpload $model): bool
    {
        try {
            $this->parseData($model->id, $model->path, function ($data) use ($model) {
                if (!isset($data['transaction_remarks'], $data['total_amount'])) {
                    return false;
                }
                $data['file_hash'] = $model->hash;
                // 格式化$data['pymt_date'], 由  24-09-2024 => 2024-09-24
                $data['pymt_date'] = date('Y-m-d', strtotime($data['pymt_date']));

                $data['order_no'] = $data['transaction_remarks'];
                $data['created_at'] = date('Y-m-d H:i:s');
                $data['created_by'] = $model->created_by;
                $data['upload_id'] = $model->id;
                $bill_data = $this->repository->create($data);
                if ($bill_data) {
                    // 判断支付状态
                    $statusValue = strtoupper(trim($data['transaction_status'] ?? ''));
                    switch ($statusValue) {
                        case 'SUC':
                            $model->increment('success_count');
                            $payment_status = DisbursementOrderVerificationQueue::PAY_STATUS_SUCCESS;
                            break;
                        case 'P':
                            $model->increment('pending_count');
                            $payment_status = DisbursementOrderVerificationQueue::PAY_STATUS_PAYING;
                            break;
                        default:
                            $model->increment('failure_count');
                            $payment_status = DisbursementOrderVerificationQueue::PAY_STATUS_FAIL;
                            break;
                    }
                    return [
                        'order_no'         => $data['order_no'],
                        'amount'           => $data['total_amount'],
                        'utr'              => $data['host_reference_number'] ?? '',
                        'payment_status'   => $payment_status,
                        'rejection_reason' => $data['transaction_status_remarks'] ?? '',
                    ];

                }
                return false;
            });

        } catch (\Throwable $e) {
            var_dump('导入icici2账单异常错误：', $e->getMessage());
            throw $e;
        }
        return false;
    }
}