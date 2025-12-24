<?php

namespace app\model;

use Carbon\Carbon;

/**
* @property int $id 主键 主键
* @property int $group_id 群组ID
* @property string $prize_serial_no 开奖流水号(格式: WIN+群ID+日期时间)
* @property int $wallet_cycle 钱包周期（对应wallet_change_count）
* @property string $ticket_number 中奖凭证
* @property int $winner_node_id_first 中奖节点ID（首）
* @property int $winner_node_id_last 中奖节点ID（尾）
* @property string $winner_node_ids 中奖区间所有节点ID（逗号分割）
* @property float $total_amount 区间总金额
* @property float $platform_fee 平台抽成
* @property float $fee_rate 手续费比例（记录当时费率）
* @property float $prize_pool 奖池金额
* @property float $prize_amount 派奖金额（奖池-平台抽成）
* @property float $prize_per_winner 每人奖金
* @property float $pool_remaining 奖池剩余金额（扣除本次中奖后余额）
* @property int $winner_count 中奖人数
* @property int $status 状态:1=待处理,2=转账中,3=已完成,4=失败,5=部分失败
* @property int $version 乐观锁版本号
* @property Carbon $created_at 创建时间
* @property Carbon $updated_at 更新时间
*/
final class ModelTgPrizeRecord extends BasicModel
{
    /**
     * The table associated with the model.
     * @var string
     */
    protected $table = 'tg_prize_record';

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
        'prize_serial_no',
        'wallet_cycle',
        'ticket_number',
        'winner_node_id_first',
        'winner_node_id_last',
        'winner_node_ids',
        'total_amount',
        'platform_fee',
        'fee_rate',
        'prize_pool',
        'prize_amount',
        'prize_per_winner',
        'pool_remaining',
        'winner_count',
        'status',
        'version',
        'created_at',
        'updated_at'
    ];
}
