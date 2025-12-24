<?php

namespace app\model;

/**
* @property int $bill_id 主键 自增id
* @property string $receipient_name 收款人名称
* @property string $account_number 账户号码
* @property string $ifsc_code IFSC代码
* @property string $amount 金额
* @property string $description 描述
* @property string $status 状态
* @property string $failure_reason 失败原因
* @property \Carbon\Carbon $created_at 创建时间
* @property int $created_by 创建人ID
* @property string $order_no 订单号
* @property int $upload_id 上传ID
* @property string $file_hash 上传源文件hash
*/
final class ModelBankDisbursementBillAxisNeft extends BasicModel
{
    /**
     * The table associated with the model.
     * @var string
     */
    protected $table = 'bank_disbursement_bill_axis_neft';

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
        'receipient_name',
        'account_number',
        'ifsc_code',
        'amount',
        'description',
        'status',
        'failure_reason',
        'created_at',
        'created_by',
        'order_no',
        'upload_id',
        'file_hash'
    ];
}
