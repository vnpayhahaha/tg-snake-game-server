<?php

namespace app\model;

/**
* @property int $bill_id 主键 主键ID/Primary Key
* @property string $sr_no 序号/Serial Number
* @property string $corporate_product 企业产品/Corporate Product
* @property string $payment_method 支付方式/Payment Method
* @property string $batch_no 批次号/Batch Number
* @property string $next_working_day_date 下一工作日日期/Next Working Day Date
* @property string $debit_account_no 借记账号/Debit Account Number
* @property string $corporate_account_description 企业账户描述/Corporate Account Description
* @property string $beneficiary_account_no 收款人账号/Beneficiary Account Number
* @property string $beneficiary_code 收款人代码/Beneficiary Code
* @property string $beneficiary_name 收款人姓名/Beneficiary Name
* @property string $payee_name 付款人姓名/Payee Name
* @property string $currency 币种/Currency
* @property float $amount_payable 应付金额/Amount Payable
* @property string $transaction_status 交易状态/Transaction Status
* @property string $crn_no CRN编号/CRN Number
* @property string $paid_date 支付日期/Paid Date
* @property string $utr_reference_no UTR/RBI参考号/核心参考号/UTR/RBI Reference No./Core Ref No.
* @property string $funding_date 资金日期/Funding Date
* @property string $reason 原因/Reason
* @property string $remarks 备注/Remarks
* @property string $stage 阶段/Stage
* @property string $email_id 邮箱/Email ID
* @property string $clg_branch_name CLG分行名称/CLG Branch Name
* @property string $activation_date 激活日期/Activation Date
* @property string $payout_mode 支付模式/Payout Mode
* @property string $finacle_cheque_no Finacle支票号/Finacle Cheque No
* @property string $ifsc_code IFSC代码/MICR代码/IIN/IFSC Code/MICR Code/IIN
* @property string $bank_reference_no 银行参考号/Bank Reference No.
* @property string $account_number 账号/Account Number
* @property int $created_by 创建人ID
* @property string $order_no 订单号
* @property int $upload_id 上传ID
* @property string $file_hash 上传源文件hash
* @property \Carbon\Carbon $created_at 创建时间/Create Time
* @property \Carbon\Carbon $updated_at 更新时间/Update Time
*/
final class ModelBankDisbursementBillAxis extends BasicModel
{
    /**
     * The table associated with the model.
     * @var string
     */
    protected $table = 'bank_disbursement_bill_axis';

    /**
     * The primary key associated with the table.
     * @var string
     */
    protected $primaryKey = 'bill_id';

    /**
     * @var bool
     */
    public $timestamps = false;
    
    /**
     * The attributes that are mass assignable.
     * @var array
     */
    protected $fillable = [
        'bill_id',
        'sr_no',
        'corporate_product',
        'payment_method',
        'batch_no',
        'next_working_day_date',
        'debit_account_no',
        'corporate_account_description',
        'beneficiary_account_no',
        'beneficiary_code',
        'beneficiary_name',
        'payee_name',
        'currency',
        'amount_payable',
        'transaction_status',
        'crn_no',
        'paid_date',
        'utr_reference_no',
        'funding_date',
        'reason',
        'remarks',
        'stage',
        'email_id',
        'clg_branch_name',
        'activation_date',
        'payout_mode',
        'finacle_cheque_no',
        'ifsc_code',
        'bank_reference_no',
        'account_number',
        'created_by',
        'order_no',
        'upload_id',
        'file_hash',
        'created_at',
        'updated_at'
    ];
}
