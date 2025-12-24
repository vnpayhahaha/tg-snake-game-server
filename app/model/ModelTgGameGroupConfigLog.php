<?php

namespace app\model;

use Carbon\Carbon;

/**
* @property int $id 主键 主键
* @property int $config_id 配置表ID
* @property int $tg_chat_id Telegram群组ID
* @property string $change_params 变更参数（JSON格式，记录本次提交的字段）
* @property string $old_config 变更前的完整配置（JSON格式）
* @property string $new_config 变更后的完整配置（JSON格式）
* @property string $operator 操作人
* @property string $operator_ip 操作IP
* @property int $change_source 变更来源:1=后台编辑,2=TG群指令
* @property int $tg_message_id Telegram消息ID（仅TG指令时有值）
* @property Carbon $created_at 变更时间
*/
final class ModelTgGameGroupConfigLog extends BasicModel
{
    /**
     * The table associated with the model.
     * @var string
     */
    protected $table = 'tg_game_group_config_log';

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
        'config_id',
        'tg_chat_id',
        'change_params',
        'old_config',
        'new_config',
        'operator',
        'operator_ip',
        'change_source',
        'tg_message_id',
        'created_at'
    ];
}
