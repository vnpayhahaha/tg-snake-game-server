<?php

namespace app\model;

use Carbon\Carbon;

/**
 * @property int $id 主键 收款凭证主键ID
 * @property int $channel_id 渠道ID
 * @property int $channel_account_id 关联channel_account.id
 * @property int $bank_account_id 关联bank_account.id
 * @property string $collection_card_no 收款卡编号
 * @property float $collection_amount 收款金额
 * @property float $collection_fee 收款手续费
 * @property Carbon $collection_time 收款时间
 * @property int $collection_status 状态(1等待核销 2已经核销 3核销失败)
 * @property int $collection_source 转账凭证来源:0未定义1人工创建2平台内部接口(sms)3平台开放下游接口(openApp)4上游回调接口
 * @property string $transaction_voucher 转账的凭证UTR/order_no/金额/上游回调原始数据
 * @property int $transaction_voucher_type 转账凭证类型：1utr 2订单id 3平台订单号 platform_order_no 4金额 5上游订单号 upstream_order_no
 * @property string $order_no 匹配的订单编号
 * @property string $content 原始内容
 * @property int $operation_admin_id 操作管理员
 * @property Carbon $created_at 创建时间
 * @property Carbon $updated_at 更新时间
 * @property int $transaction_type 交易类型：1代收 2代付
 */
final class ModelTransactionVoucher extends BasicModel
{
    /**
     * The table associated with the model.
     * @var string
     */
    protected $table = 'transaction_voucher';

    /**
     * The primary key associated with the table.
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * The attributes that are mass assignable.
     * @var array
     */
    protected $fillable = [
        'channel_id',
        'channel_account_id',
        'bank_account_id',
        'collection_card_no',
        'collection_amount',
        'collection_fee',
        'collection_time',
        'collection_status',
        'collection_source',
        'transaction_voucher',
        'transaction_voucher_type',
        'order_no',
        'content',
        'operation_admin_id',
        'created_at',
        'updated_at',
        'transaction_type'
    ];

    protected $casts = [
        'channel_id'               => 'integer',
        'channel_account_id'       => 'integer',
        'bank_account_id'          => 'integer',
        'collection_amount'        => 'float',
        'collection_fee'           => 'float',
        'collection_time'          => 'datetime',
        'collection_status'        => 'integer',
        'collection_source'        => 'integer',
        'transaction_voucher_type' => 'integer',
        'order_no'                 => 'string',
        'content'                  => 'string',
        'operation_admin_id'       => 'integer',
        'created_at'               => 'datetime',
        'updated_at'               => 'datetime',
        'transaction_type'         => 'integer'
    ];

    public function channel()
    {
        return $this->belongsTo(ModelChannel::class, 'channel_id','id' );
    }
    public function channel_account()
    {
        return $this->belongsTo(ModelChannelAccount::class, 'channel_account_id','id' );
    }
    public function bank_account()
    {
        return $this->belongsTo(ModelBankAccount::class, 'bank_account_id','id' );
    }
}
