<?php

namespace app\model;

use Carbon\Carbon;

/**
 * @property int $id 主键 主键ID
 * @property string $transaction_no 关联交易流水号
 * @property int $transaction_type 冗余业务交易类型（便于按类型调度）
 * @property int $process_status 状态:0-处理中 1-成功 2-失败 3-挂起
 * @property Carbon $scheduled_execute_time 计划执行时间
 * @property Carbon $next_retry_time 下次重试时间
 * @property int $retry_count 重试次数
 * @property int $lock_version 乐观锁版本号
 * @property string $error_code 错误代码
 * @property string $error_detail 错误详情
 * @property Carbon $created_at 创建时间
 * @property Carbon $updated_at 更新时间
 */
final class ModelTransactionQueueStatus extends BasicModel
{
    /**
     * The table associated with the model.
     * @var string
     */
    protected $table = 'transaction_queue_status';

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
        'transaction_no',
        'transaction_type',
        'process_status',
        'scheduled_execute_time',
        'next_retry_time',
        'retry_count',
        'lock_version',
        'error_code',
        'error_detail',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'id'                     => 'integer',
        'transaction_no'         => 'string',
        'transaction_type'       => 'integer',
        'process_status'         => 'integer',
        'scheduled_execute_time' => 'datetime',
        'next_retry_time'        => 'datetime',
        'retry_count'            => 'integer',
        'lock_version'           => 'integer',
        'error_code'             => 'string',
        'error_detail'           => 'string',
        'created_at'             => 'datetime',
        'updated_at'             => 'datetime',
    ];

}
