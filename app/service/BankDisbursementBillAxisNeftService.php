<?php

namespace app\service;

use app\constants\DisbursementOrderVerificationQueue;
use app\model\ModelBankDisbursementUpload;
use app\repository\BankDisbursementBillAxisNeftRepository;
use app\service\handle\BankDisbursementBillAbstract;
use DI\Attribute\Inject;

class BankDisbursementBillAxisNeftService extends BankDisbursementBillAbstract
{
    #[Inject]
    public BankDisbursementBillAxisNeftRepository $repository;

    protected array $FieldMap = [
        'receipientname' => 'receipient_name',
        'accountnumber'  => 'account_number',
        'ifsccode'       => 'ifsc_code',
        'amount'         => 'amount',
        'description'    => 'description',
        'status'         => 'status',
        'failurereason'  => 'failure_reason',
    ];

    public function importBill(ModelBankDisbursementUpload $model): bool
    {
        try {
            $this->parseData($model->id, $model->path, function ($data) use ($model) {
                if (!isset($data['description'], $data['status'], $data['amount'])) {
                    return false;
                }
                $data['file_hash'] = $model->hash;
                $data['amount'] = str_replace(',', '', $data['amount']);

                $data['order_no'] = $data['description'];
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
                        default:
                            $model->increment('failure_count');
                            $payment_status = DisbursementOrderVerificationQueue::PAY_STATUS_FAIL;
                            break;
                    }
                    return [
                        'order_no'         => $data['order_no'],
                        'amount'           => $data['amount'],
                        'utr'              => '',
                        'payment_status'   => $payment_status,
                        'rejection_reason' => $data['failure_reason'] ?? '',
                    ];
                }
                return false;
            });

        } catch (\Throwable $e) {
            var_dump('导入axis neft账单异常错误：', $e->getMessage());
            throw $e;
        }
        return false;
    }


}