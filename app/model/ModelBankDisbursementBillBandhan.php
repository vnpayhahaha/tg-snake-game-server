<?php

namespace app\model;

use Carbon\Carbon;

/**
* @property int $bill_id 主键 自增id
* @property string $core_ref_number 核心参考号
* @property string $status 状态
* @property Carbon $execution_time 执行时间
* @property string $error_code 错误代码
* @property Carbon $payment_date 付款日期
* @property string $payment_type 付款类型
* @property string $customer_ref_number 客户参考号
* @property string $source_account_number 源账户号码
* @property string $source_narration 源账户说明
* @property string $destination_account_number 目标账户号码
* @property string $currency 币种
* @property float $amount 金额
* @property string $destination_narration 目标账户说明
* @property string $destination_bank 目标银行
* @property string $destination_bank_routing_code 目标银行路由代码
* @property string $beneficiary_name 受益人名称
* @property string $beneficiary_code 受益人代码
* @property string $beneficiary_account_type 受益人账户类型
* @property Carbon $created_at 创建时间
* @property int $created_by 创建人ID
* @property string $order_no 订单号
* @property int $upload_id 上传ID
* @property string $file_hash 上传源文件hash
* @property string $rejection_reason 拒绝原因
*/
final class ModelBankDisbursementBillBandhan extends BasicModel
{
    /**
     * The table associated with the model.
     * @var string
     */
    protected $table = 'bank_disbursement_bill_bandhan';

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
        'core_ref_number',
        'status',
        'execution_time',
        'error_code',
        'payment_date',
        'payment_type',
        'customer_ref_number',
        'source_account_number',
        'source_narration',
        'destination_account_number',
        'currency',
        'amount',
        'destination_narration',
        'destination_bank',
        'destination_bank_routing_code',
        'beneficiary_name',
        'beneficiary_code',
        'beneficiary_account_type',
        'created_at',
        'created_by',
        'order_no',
        'upload_id',
        'file_hash',
        'rejection_reason'
    ];
}
