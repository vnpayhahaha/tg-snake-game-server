<?php

namespace app\service;

use app\constants\DisbursementOrderVerificationQueue;
use app\model\ModelBankDisbursementUpload;
use app\repository\BankDisbursementBillYesmsmeRepository;
use app\service\handle\BankDisbursementBillAbstract;
use DI\Attribute\Inject;

class BankDisbursementBillYesmsmeService extends BankDisbursementBillAbstract
{
    #[Inject]
    public BankDisbursementBillYesmsmeRepository $repository;

    protected array $FieldMap = [
        'record'            => 'record',
        'recordrefno'       => 'record_ref_no',
        'filerefno'         => 'file_ref_no',
        'e-bankingrefno'    => 'ebanking_ref_no',
        'contractrefno'     => 'contract_ref_no',
        'recordstatus'      => 'record_status',
        'statuscode'        => 'status_code',
        'statusdescription' => 'status_description',
    ];

    public function importBill(ModelBankDisbursementUpload $model): bool
    {
        try {
            $this->parseData($model->id, $model->path, function ($data) use ($model) {
                if (!isset($data['record'], $data['record_ref_no'], $data['record_status'])) {
                    return false;
                }
                $data['file_hash'] = $model->hash;
                $recordArr = explode('~', $data['record']);
                $remarkArr = explode('-', $recordArr[7]);
                if (count($recordArr) !== 8) {
                    $model->increment('failure_count');
                    return false;
                }

                $data['order_no'] = $remarkArr[1] ?? '';
                if (!filled($data['order_no '])) {
                    $model->increment('failure_count');
                    return false;
                }
                $data['created_at'] = date('Y-m-d H:i:s');
                $data['created_by'] = $model->created_by;
                $data['upload_id'] = $model->id;
                $bill_data = $this->repository->create($data);
                if ($bill_data) {
                    // 判断支付状态
                    $statusValue = strtoupper(trim($data['record_status']));
                    switch ($statusValue) {
                        case 'COMPLETED':
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
                        'amount'           => str_replace(',', '', $recordArr[4]) ?? '',
                        'utr'              => $data['record_ref_no'],
                        'payment_status'   => $payment_status,
                        'rejection_reason' => $data['status_description'] ?? '',
                    ];

                }
                return false;
            });

        } catch (\Throwable $e) {
            var_dump('导入yes msme账单异常错误：', $e->getMessage());
            throw $e;
        }
        return false;
    }
}