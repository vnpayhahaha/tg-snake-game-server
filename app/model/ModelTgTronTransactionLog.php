<?php

namespace app\model;

use Carbon\Carbon;

/**
* @property int $id 主键 主键
* @property int $group_id 群组ID
* @property string $tx_hash 交易哈希
* @property string $from_address 发送地址
* @property string $to_address 接收地址
* @property float $amount 金额(TRX)
* @property int $transaction_type 交易类型:1=入账,2=出账
* @property int $block_height 区块高度
* @property int $block_timestamp 区块时间戳
* @property string $status 交易状态
* @property int $is_valid 是否有效交易
* @property string $invalid_reason 无效原因
* @property int $processed 是否已处理
* @property Carbon $created_at 创建时间
*/
final class ModelTgTronTransactionLog extends BasicModel
{
    /**
     * The table associated with the model.
     * @var string
     */
    protected $table = 'tg_tron_transaction_log';

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
        'group_id',
        'tx_hash',
        'from_address',
        'to_address',
        'amount',
        'transaction_type',
        'block_height',
        'block_timestamp',
        'status',
        'is_valid',
        'invalid_reason',
        'processed',
        'created_at'
    ];
}
