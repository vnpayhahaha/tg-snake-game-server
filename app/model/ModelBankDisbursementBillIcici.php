<?php

namespace app\model;

use Carbon\Carbon;

/**
* @property int $bill_id 主键 自增id
* @property string $pymt_mode 支付模式(IMPS)
* @property string $file_sequence_num 文件序列号
* @property string $debit_acct_no 借记账户号码
* @property string $beneficiary_name 受益人名称
* @property string $beneficiary_account_no 受益人账户号
* @property string $bene_ifsc_code 受益人IFSC代码
* @property float $amount 金额
* @property string $remark 备注
* @property Carbon $pymt_date 支付日期
* @property string $status 状态
* @property string $rejection_reason 拒绝原因
* @property string $customer_ref_no 客户编号
* @property string $utr_no UTR_NO
* @property string $order_no 订单号
* @property int $upload_id 上传ID
* @property string $file_hash 上传源文件hash
* @property Carbon $created_at 创建时间
* @property int $created_by 创建人ID
*/
final class ModelBankDisbursementBillIcici extends BasicModel
{
    /**
     * The table associated with the model.
     * @var string
     */
    protected $table = 'bank_disbursement_bill_icici';

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
        'pymt_mode',
        'file_sequence_num',
        'debit_acct_no',
        'beneficiary_name',
        'beneficiary_account_no',
        'bene_ifsc_code',
        'amount',
        'remark',
        'pymt_date',
        'status',
        'rejection_reason',
        'customer_ref_no',
        'utr_no',
        'order_no',
        'upload_id',
        'file_hash',
        'created_at',
        'created_by'
    ];
}
