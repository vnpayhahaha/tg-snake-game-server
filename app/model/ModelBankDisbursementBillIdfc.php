<?php

namespace app\model;

/**
* @property int $bill_id 主键 自增id
* @property string $beneficiary_name 收款人姓名/Beneficiary Name
* @property string $beneficiary_account_number 收款人账号/Beneficiary Account Number
* @property string $ifsc IFSC代码
* @property string $transaction_type 交易类型/Transaction Type
* @property string $debit_account_no 借记账号/Debit Account No
* @property string $transaction_date 交易日期/Transaction Date
* @property float $amount 金额/Amount
* @property string $currency 币种/Currency
* @property string $beneficiary_email_id 收款人邮箱/Beneficiary Email ID
* @property string $remarks 备注/Remarks
* @property string $utr_number UTR编号/UTR Number
* @property string $status 状态/Status
* @property string $errors 错误信息/Errors
* @property \Carbon\Carbon $created_at 创建时间
* @property int $created_by 创建人ID
* @property string $order_no 订单号
* @property int $upload_id 上传ID
* @property string $file_hash 上传源文件hash
*/
final class ModelBankDisbursementBillIdfc extends BasicModel
{
    /**
     * The table associated with the model.
     * @var string
     */
    protected $table = 'bank_disbursement_bill_idfc';

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
        'beneficiary_name',
        'beneficiary_account_number',
        'ifsc',
        'transaction_type',
        'debit_account_no',
        'transaction_date',
        'amount',
        'currency',
        'beneficiary_email_id',
        'remarks',
        'utr_number',
        'status',
        'errors',
        'created_at',
        'created_by',
        'order_no',
        'upload_id',
        'file_hash'
    ];
}
