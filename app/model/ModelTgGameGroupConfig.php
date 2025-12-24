<?php

namespace app\model;

use Carbon\Carbon;

/**
* @property int $id 主键 主键
* @property string $tenant_id 租户ID
* @property int $tg_chat_id Telegram群组ID
* @property string $tg_chat_title 群组名称
* @property string $wallet_address TRON钱包地址
* @property int $wallet_change_count 钱包变更次数（用于区分不同钱包周期）
* @property string $pending_wallet_address 待更新的钱包地址
* @property int $wallet_change_status 钱包变更状态:1=正常,2=变更中
* @property Carbon $wallet_change_start_at 钱包变更开始时间
* @property Carbon $wallet_change_end_at 钱包变更生效时间
* @property string $hot_wallet_address 热钱包地址（用于转账）
* @property string $hot_wallet_private_key 热钱包私钥（加密存储）
* @property float $bet_amount 投注金额(TRX)
* @property float $platform_fee_rate 平台手续费比例(默认10%)
* @property int $status 状态 1-正常 0-停用
* @property Carbon $created_at 创建时间
* @property Carbon $updated_at 更新时间
*/
final class ModelTgGameGroupConfig extends BasicModel
{
    /**
     * The table associated with the model.
     * @var string
     */
    protected $table = 'tg_game_group_config';

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
        'tenant_id',
        'tg_chat_id',
        'tg_chat_title',
        'wallet_address',
        'wallet_change_count',
        'pending_wallet_address',
        'wallet_change_status',
        'wallet_change_start_at',
        'wallet_change_end_at',
        'hot_wallet_address',
        'hot_wallet_private_key',
        'bet_amount',
        'platform_fee_rate',
        'status',
        'created_at',
        'updated_at'
    ];
}
