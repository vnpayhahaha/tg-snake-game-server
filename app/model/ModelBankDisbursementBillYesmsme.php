<?php

namespace app\model;

/**
* @property int $bill_id 主键 自增id
* @property string $record 记录
* @property string $record_ref_no 记录参考号
* @property string $file_ref_no 文件参考号
* @property string $ebanking_ref_no 电子银行参考号
* @property string $contract_ref_no 合同参考号
* @property string $record_status 记录状态
* @property string $status_code 状态代码
* @property string $status_description 状态描述
* @property \Carbon\Carbon $created_at 创建时间
* @property int $created_by 创建人ID
* @property string $order_no 订单号
* @property int $upload_id 上传ID
* @property string $file_hash 上传源文件hash
*/
final class ModelBankDisbursementBillYesmsme extends BasicModel
{
    /**
     * The table associated with the model.
     * @var string
     */
    protected $table = 'bank_disbursement_bill_yesmsme';

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
        'record',
        'record_ref_no',
        'file_ref_no',
        'ebanking_ref_no',
        'contract_ref_no',
        'record_status',
        'status_code',
        'status_description',
        'created_at',
        'created_by',
        'order_no',
        'upload_id',
        'file_hash'
    ];
}
