<?php

namespace app\service;

use app\constants\DisbursementOrderVerificationQueue;
use app\model\ModelBankDisbursementUpload;
use app\repository\BankDisbursementBillAxisNeoRepository;
use app\service\handle\BankDisbursementBillAbstract;
use DI\Attribute\Inject;

class BankDisbursementBillAxisNeoService extends BankDisbursementBillAbstract
{
    #[Inject]
    public BankDisbursementBillAxisNeoRepository $repository;


    protected array $FieldMap = [
        'srlno'        => 'srl_no',
        'trandate'     => 'tran_date',
        'chqno'        => 'chq_no',
        'particulars'  => 'particulars',
        'amount(inr)'  => 'amount_inr',
        'dr/cr'        => 'dr_cr',
        'balance(inr)' => 'balance_inr',
        'sol'          => 'sol',
    ];

    public function importBill(ModelBankDisbursementUpload $model): bool
    {
        try {
            $this->parseData($model->id, $model->path, function ($data) use ($model) {
                // dump($data);
                if (!isset($data['particulars'], $data['dr_cr'], $data['amount_inr'], $data['tran_date']) ||
                    !filled($data['particulars']) ||
                    !filled($data['dr_cr']) ||
                    $data['dr_cr'] !== 'DR' ||
                    !filled($data['amount_inr']) ||
                    !filled($data['tran_date'])
                ) {
                    return false;
                }
                $data['file_hash'] = $model->hash;
                // 随机字符串
                $particulars_parse = explode('/', $data['particulars']);
                $order_no = $particulars_parse[3];

                $data['order_no'] = $order_no;
                $data['created_at'] = date('Y-m-d H:i:s');
                $data['created_by'] = $model->created_by;
                $data['upload_id'] = $model->id;
                $bill_data = $this->repository->create($data);
                if ($bill_data) {
                    $model->increment('success_count');
                    $payment_status = DisbursementOrderVerificationQueue::PAY_STATUS_SUCCESS;
                    return [
                        'order_no'         => $data['order_no'],
                        'amount'           => $data['amount_inr'],
                        'utr'              => $particulars_parse[2],
                        'payment_status'   => $payment_status,
                        'rejection_reason' => $data['sol'] ?? '',
                    ];
                }
                return false;
            });

        } catch (\Throwable $e) {
            var_dump('导入axis neo账单异常错误：', $e->getMessage());
            throw $e;
        }
        return false;
    }
}