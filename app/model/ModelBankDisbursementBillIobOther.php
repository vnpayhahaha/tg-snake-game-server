<?php

namespace app\model;

/**
* @property int $bill_id 主键 自增id
* @property string $s_no 序号
* @property string $name 姓名
* @property string $ifsc_code IFSC代码
* @property string $type 类型
* @property string $number 编号
* @property string $amount 金额
* @property string $charges 费用
* @property string $status 状态
* @property string $remarks 备注
* @property string $narration 说明
* @property string $utr_no UTR编号
* @property string $reason 原因
* @property \Carbon\Carbon $created_at 创建时间
* @property int $created_by 创建人ID
* @property string $order_no 订单号
* @property int $upload_id 上传ID
* @property string $file_hash 上传源文件hash
* @property string $rejection_reason 拒绝原因
*/
final class ModelBankDisbursementBillIobOther extends BasicModel
{
    /**
     * The table associated with the model.
     * @var string
     */
    protected $table = 'bank_disbursement_bill_iob_other';

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
        's_no',
        'name',
        'ifsc_code',
        'type',
        'number',
        'amount',
        'charges',
        'status',
        'remarks',
        'narration',
        'utr_no',
        'reason',
        'created_at',
        'created_by',
        'order_no',
        'upload_id',
        'file_hash',
        'rejection_reason'
    ];
}
