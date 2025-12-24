<?php

namespace app\model;

use Carbon\Carbon;

/**
* @property int $id 主键 主键
* @property int $prize_record_id 中奖记录ID
* @property string $prize_serial_no 开奖流水号
* @property int $node_id 中奖节点ID
* @property string $to_address 收款地址
* @property float $amount 转账金额
* @property string $tx_hash 转账交易哈希
* @property int $status 状态:1=待处理,2=处理中,3=成功,4=失败
* @property int $retry_count 重试次数
* @property string $error_message 错误信息
* @property Carbon $created_at 创建时间
* @property Carbon $updated_at 更新时间
*/
final class ModelTgPrizeTransfer extends BasicModel
{
    /**
     * The table associated with the model.
     * @var string
     */
    protected $table = 'tg_prize_transfer';

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
        'prize_record_id',
        'prize_serial_no',
        'node_id',
        'to_address',
        'amount',
        'tx_hash',
        'status',
        'retry_count',
        'error_message',
        'created_at',
        'updated_at'
    ];
}
