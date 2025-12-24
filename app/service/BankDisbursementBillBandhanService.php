<?php

namespace app\service;

use app\constants\DisbursementOrderVerificationQueue;
use app\model\ModelBankDisbursementUpload;
use app\repository\BankDisbursementBillBandhanRepository;
use app\service\handle\BankDisbursementBillAbstract;
use DI\Attribute\Inject;

class BankDisbursementBillBandhanService extends BankDisbursementBillAbstract
{
    #[Inject]
    public BankDisbursementBillBandhanRepository $repository;

    protected array $FieldMap = [
        'corerefnumber'              => 'core_ref_number',
        'status'                     => 'status',
        'executiontime'              => 'execution_time',
        'errorcode'                  => 'error_code',
        'paymentdate'                => 'payment_date',
        'paymenttype'                => 'payment_type',
        'customerrefnumber'          => 'customer_ref_number',
        'sourceaccountnumber'        => 'source_account_number',
        'sourcenarration'            => 'source_narration',
        'destinationaccountnumber'   => 'destination_account_number',
        'currency'                   => 'currency',
        'amount'                     => 'amount',
        'destinationnarration'       => 'destination_narration',
        'destinationbank'            => 'destination_bank',
        'destinationbankroutingcode' => 'destination_bank_routing_code',
        'beneficiaryname'            => 'beneficiary_name',
        'beneficiarycode'            => 'beneficiary_code',
        'beneficiaryaccounttype'     => 'beneficiary_account_type',
    ];

    public function importBill(ModelBankDisbursementUpload $model): bool
    {
        try {
            $this->parseData($model->id, $model->path, function ($data) use ($model) {
                if (!isset($data['destination_narration'], $data['amount'])) {
                    return false;
                }
                $data['file_hash'] = $model->hash;
                // 判断 是否设置execution_time 并且格式 2025-05-1419:20:15，格式化成 2025-05-14 19:20:15
                if (isset($data['execution_time'])) {
                    $data['execution_time'] = date('Y-m-d H:i:s', strtotime($data['execution_time']));
                }
                // 创建 DateTime 对象
                $payment_date = date('Y-m-d H:i:s');
                // 尝试将字符串转换为 DateTime 对象
                if (filled($data['payment_date']) && $dateTime = \DateTime::createFromFormat('Y-m-dH:i:s', $data['payment_date'])) {
                    $payment_date = $dateTime->format('Y-m-d H:i:s');
                }
                $data['payment_date'] = $payment_date;

                $data['order_no'] = $data['destination_narration'];
                $data['created_at'] = date('Y-m-d H:i:s');
                $data['created_by'] = $model->created_by;
                $data['upload_id'] = $model->id;
                $bill_data = $this->repository->create($data);
                if ($bill_data) {
                    // 判断支付状态
                    $statusValue = strtoupper(trim($data['status'] ?? ''));
                    switch ($statusValue) {
                        case 'SUCCESS':
                            $model->increment('success_count');
                            $payment_status = DisbursementOrderVerificationQueue::PAY_STATUS_SUCCESS;
                            break;
                        case 'PENDING':
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
                        'amount'           => $data['amount'],
                        'utr'              => $data['core_ref_number'] ?? '',
                        'payment_status'   => $payment_status,
                        'rejection_reason' => $data['transaction_status_remarks'] ?? '',
                    ];

                }
                return false;
            });

        } catch (\Throwable $e) {
            var_dump('导入bandhan账单异常错误：', $e->getMessage());
            throw $e;
        }
        return false;
    }
}