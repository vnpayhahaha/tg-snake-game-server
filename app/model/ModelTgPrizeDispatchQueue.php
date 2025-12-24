<?php

namespace app\model;

use Carbon\Carbon;

/**
* @property int $id 主键 主键
* @property int $prize_record_id 中奖记录ID
* @property int $prize_transfer_id 转账记录ID
* @property int $group_id 群组ID
* @property string $prize_serial_no 开奖流水号
* @property int $priority 优先级(1-10,数字越小优先级越高)
* @property int $status 状态:1=待处理,2=处理中,3=已完成,4=失败,5=取消
* @property int $retry_count 重试次数
* @property int $max_retry 最大重试次数
* @property string $task_data 任务数据(JSON格式)
* @property string $error_message 错误信息
* @property Carbon $scheduled_at 计划执行时间
* @property Carbon $started_at 开始处理时间
* @property Carbon $completed_at 完成时间
* @property int $version 乐观锁版本号
* @property Carbon $created_at 创建时间
* @property Carbon $updated_at 更新时间
*/
final class ModelTgPrizeDispatchQueue extends BasicModel
{
    /**
     * The table associated with the model.
     * @var string
     */
    protected $table = 'tg_prize_dispatch_queue';

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
        'prize_transfer_id',
        'group_id',
        'prize_serial_no',
        'priority',
        'status',
        'retry_count',
        'max_retry',
        'task_data',
        'error_message',
        'scheduled_at',
        'started_at',
        'completed_at',
        'version',
        'created_at',
        'updated_at'
    ];
}
