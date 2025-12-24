<?php

namespace app\model;

/**
* @property int $bill_id 主键 自增id
* @property string $srl_no 序列号
* @property string $tran_date 交易日期
* @property string $chq_no 支票号
* @property string $particulars 摘要
* @property float $amount_inr 金额(INR)
* @property string $dr_cr 借/贷
* @property float $balance_inr 余额(INR)
* @property string $sol SOL
* @property \Carbon\Carbon $created_at 创建时间
* @property int $created_by 创建人ID
* @property string $order_no 订单号
* @property int $upload_id 上传ID
* @property string $file_hash 上传源文件hash
*/
final class ModelBankDisbursementBillAxisNeo extends BasicModel
{
    /**
     * The table associated with the model.
     * @var string
     */
    protected $table = 'bank_disbursement_bill_axis_neo';

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
        'srl_no',
        'tran_date',
        'chq_no',
        'particulars',
        'amount_inr',
        'dr_cr',
        'balance_inr',
        'sol',
        'created_at',
        'created_by',
        'order_no',
        'upload_id',
        'file_hash'
    ];
}
