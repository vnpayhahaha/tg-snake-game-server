<?php

namespace app\model;

use Carbon\Carbon;

/**
 * @property int $id 主键
 * @property string $request_id 请求ID
 * @property mixed $order_data 订单数据
 * @property int $process_status 处理状态:
 * 0-待处理 1-处理中 2-成功 3-失败
 * @property int $retry_count 重试次数
 * @property Carbon $next_retry_time 下次重试时间
 * @property string $error_message 错误信息
 * @property Carbon $created_at
 * @property Carbon $processed_at
 */
final class ModelDisbursementOrderQueue extends BasicModel
{
    /**
     * The table associated with the model.
     * @var string
     */
    protected $table = 'disbursement_order_queue';

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
        'request_id',
        'order_data',
        'process_status',
        'retry_count',
        'next_retry_time',
        'error_message',
        'created_at',
        'processed_at'
    ];

    protected $casts = [
        'process_status' => 'integer',
        'retry_count'    => 'integer',
        'order_data'     => 'json',
        'created_at'     => 'datetime',
        'processed_at'   => 'datetime',
    ];
}
