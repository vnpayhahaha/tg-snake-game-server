<?php

namespace app\model;

use Carbon\Carbon;

/**
* @property int $id 主键 主键
* @property int $group_id 群组ID
* @property int $tg_user_id Telegram用户ID
* @property string $tg_username Telegram用户名
* @property string $tg_first_name Telegram名字
* @property string $tg_last_name Telegram姓氏
* @property string $wallet_address 绑定的钱包地址
* @property Carbon $bind_at 首次绑定时间
* @property Carbon $created_at 创建时间
* @property Carbon $updated_at 更新时间
*/
final class ModelTgPlayerWalletBinding extends BasicModel
{
    /**
     * The table associated with the model.
     * @var string
     */
    protected $table = 'tg_player_wallet_binding';

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
        'tg_user_id',
        'tg_username',
        'tg_first_name',
        'tg_last_name',
        'wallet_address',
        'bind_at',
        'created_at',
        'updated_at'
    ];
}
