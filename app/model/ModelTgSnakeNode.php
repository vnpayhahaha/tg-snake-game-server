<?php

namespace app\model;

use Carbon\Carbon;

/**
* @property int $id 主键 主键
* @property int $group_id 群组ID
* @property int $wallet_cycle 钱包周期（对应wallet_change_count）
* @property string $ticket_number 购彩凭证(00-99)
* @property string $ticket_serial_no 凭证流水号(格式: YYYYMMDD-序号，如: 20250108-001)
* @property string $wallet_address 收款地址
* @property string $player_address 玩家钱包地址
* @property string $player_tg_username Telegram用户名
* @property int $player_tg_user_id Telegram用户ID
* @property float $amount 投注金额
* @property string $tx_hash 交易哈希
* @property int $block_height 区块高度
* @property int $daily_sequence 当天第几笔交易（从1开始）
* @property int $status 状态:1=活跃,2=已中奖,3=未中奖
* @property int $matched_prize_id 匹配的中奖记录ID
* @property Carbon $created_at 创建时间
*/
final class ModelTgSnakeNode extends BasicModel
{
    /**
     * The table associated with the model.
     * @var string
     */
    protected $table = 'tg_snake_node';

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
        'wallet_cycle',
        'ticket_number',
        'ticket_serial_no',
        'wallet_address',
        'player_address',
        'player_tg_username',
        'player_tg_user_id',
        'amount',
        'tx_hash',
        'block_height',
        'daily_sequence',
        'status',
        'matched_prize_id',
        'created_at'
    ];
}
