<?php

namespace app\model;

use Carbon\Carbon;

/**
* @property int $bill_id 主键 自增id
* @property string $network_id 网络ID
* @property string $credit_account_number 贷方账户号码
* @property string $debit_account_number 借方账户号码
* @property string $ifsc_code IFSC代码
* @property float $total_amount 总金额
* @property string $host_reference_number 主机参考号码
* @property string $transaction_remarks 交易备注
* @property string $transaction_status 交易状态
* @property string $transaction_status_remarks 交易状态备注
* @property Carbon $created_at 创建时间
* @property int $created_by 创建人ID
* @property string $order_no 订单号
* @property int $upload_id 上传ID
* @property string $file_hash 上传源文件hash
* @property string $rejection_reason 拒绝原因
*/
final class ModelBankDisbursementBillIcici2 extends BasicModel
{
    /**
     * The table associated with the model.
     * @var string
     */
    protected $table = 'bank_disbursement_bill_icici_2';

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
        'network_id',
        'credit_account_number',
        'debit_account_number',
        'ifsc_code',
        'total_amount',
        'host_reference_number',
        'transaction_remarks',
        'transaction_status',
        'transaction_status_remarks',
        'created_at',
        'created_by',
        'order_no',
        'upload_id',
        'file_hash',
        'rejection_reason'
    ];
}
