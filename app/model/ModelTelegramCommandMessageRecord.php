<?php

namespace app\model;

use Carbon\Carbon;

/**
* @property int $id 主键 记录ID
* @property int $user_id 用户ID
* @property string $username 用户账号
* @property string $nickname 用户昵称
* @property string $chat_id 聊天群id
* @property string $chat_name 群名称
* @property int $message_id 消息ID
* @property string $command 命令
* @property string $original_message 原始信息
* @property string $response_message 返回信息
* @property int $status 状态(1待处理 2已处理 3处理失败)
* @property Carbon $created_at 创建时间
* @property Carbon $updated_at 更新时间
*/
final class ModelTelegramCommandMessageRecord extends BasicModel
{
    /**
     * The table associated with the model.
     * @var string
     */
    protected $table = 'telegram_command_message_record';

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
        'user_id',
        'username',
        'nickname',
        'chat_id',
        'chat_name',
        'message_id',
        'command',
        'original_message',
        'response_message',
        'status',
        'created_at',
        'updated_at'
    ];
}
