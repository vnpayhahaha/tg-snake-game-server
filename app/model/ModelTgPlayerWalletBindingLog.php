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
* @property string $old_wallet_address 变更前钱包地址（首次绑定为空字符串）
* @property string $new_wallet_address 变更后钱包地址
* @property int $change_type 变更类型:1=首次绑定,2=更新绑定
* @property Carbon $created_at 变更时间
*/
final class ModelTgPlayerWalletBindingLog extends BasicModel
{
    /**
     * The table associated with the model.
     * @var string
     */
    protected $table = 'tg_player_wallet_binding_log';

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
        'old_wallet_address',
        'new_wallet_address',
        'change_type',
        'created_at'
    ];
}
