<?php

namespace app\service;

use app\constants\DisbursementOrderVerificationQueue;
use app\model\ModelBankDisbursementUpload;
use app\repository\BankDisbursementBillAxisRepository;
use app\service\handle\BankDisbursementBillAbstract;
use DI\Attribute\Inject;

class BankDisbursementBillAxisService extends BankDisbursementBillAbstract
{
    #[Inject]
    public BankDisbursementBillAxisRepository $repository;

    protected array $FieldMap = [
        'sr.no.'                         => 'sr_no',
        'corporateproduct'               => 'corporate_product',
        'paymentmethod'                  => 'payment_method',
        'batchno'                        => 'batch_no',
        'nextworkingdaydate'             => 'next_working_day_date',
        'debita/cno.'                    => 'debit_account_no',
        'corporateaccountdescription'    => 'corporate_account_description',
        'beneficiarya/cno.'              => 'beneficiary_account_no',
        'beneficiarycode'                => 'beneficiary_code',
        'beneficiaryname'                => 'beneficiary_name',
        'payeename'                      => 'payee_name',
        'currency'                       => 'currency',
        'amountpayable'                  => 'amount_payable',
        'transactionstatus'              => 'transaction_status',
        'crnno.'                         => 'crn_no',
        'paiddate'                       => 'paid_date',
        'utr/rbireferenceno./corerefno.' => 'utr_reference_no',
        'fundingdate'                    => 'funding_date',
        'reason'                         => 'reason',
        'remarks'                        => 'remarks',
        'stage'                          => 'stage',
        'emailid'                        => 'email_id',
        'clgbranchname'                  => 'clg_branch_name',
        'activationdate'                 => 'activation_date',
        'payoutmode'                     => 'payout_mode',
        'finaclechequeno'                => 'finacle_cheque_no',
        'ifsccode/micrcode/iin'          => 'ifsc_code',
        'bankreferenceno.'               => 'bank_reference_no',
        'accountnumber'                  => 'account_number',

    ];

    public function importBill(ModelBankDisbursementUpload $model): bool
    {
        try {
            $this->parseData($model->id, $model->path, function ($data) use ($model) {
                if (!isset($data['debit_account_no'], $data['utr_reference_no'], $data['amount_payable'], $data['stage'])) {
                    return false;
                }
                $data['file_hash'] = $model->hash;
                // 随机字符串
                $data['amount_payable'] = str_replace(',', '', $data['amount_payable']);

                $data['order_no'] = $data['remarks'];
                $data['created_at'] = date('Y-m-d H:i:s');
                $data['created_by'] = $model->created_by;
                $data['upload_id'] = $model->id;
                $bill_data = $this->repository->create($data);
                if ($bill_data) {
                    // 判断支付状态
                    $statusValue = strtoupper(trim($data['stage'] ?? ''));
                    switch ($statusValue) {
                        case 'PAID':
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
                        'amount'           => $data['amount_payable'],
                        'utr'              => $data['utr_reference_no'],
                        'payment_status'   => $payment_status,
                        'rejection_reason' => $data['reason'] ?? '',
                    ];
                }
                return false;
            });
            // 读取ModelBankDisbursementUpload判断 success_count == record_count， 更新 parsing_status
//            $model->parsing_status = $model->success_count === $model->record_count ? 1 : 0;
            var_dump('导入axis账单成功', $model->toArray());

        } catch (\Throwable $e) {
            var_dump('导入axis账单异常错误：', $e->getMessage());
            throw $e;
        }
        return false;
    }
}