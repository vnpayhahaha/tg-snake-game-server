<?php

namespace app\model;

use Carbon\Carbon;

/**
 * @property int $id 主键 主键ID
 * @property string $callback_id 回调唯一标识
 * @property int $channel_id 渠道ID
 * @property string $original_request_id 原始请求ID(关联请求记录)
 * @property string $callback_type 回调类型(如:支付结果通知、异步通知等)
 * @property string $callback_url 回调请求的完整地址
 * @property string $callback_http_method 回调请求的HTTP方法(GET/POST/PUT等)
 * @property string $callback_params 回调参数(JSON格式)
 * @property string $callback_headers 回调头信息(JSON格式)
 * @property string $callback_body 回调体内容
 * @property Carbon $callback_time 回调到达时间
 * @property string $client_ip 回调来源IP
 * @property int $status 状态: 0-接收中, 1-接收成功, 2-接收失败
 * @property string $response_content 返回给渠道的内容
 * @property string $process_result 处理结果描述
 * @property int $elapsed_time 处理耗时(毫秒)
 * @property Carbon $created_at 创建时间
 */
final class ModelChannelCallbackRecord extends BasicModel
{
    /**
     * The table associated with the model.
     * @var string
     */
    protected $table = 'channel_callback_record';

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
        'callback_id',
        'channel_id',
        'original_request_id',
        'callback_type',
        'callback_url',
        'callback_http_method',
        'callback_params',
        'callback_headers',
        'callback_body',
        'callback_time',
        'client_ip',
        'status',
        'response_content',
        'process_result',
        'elapsed_time',
        'created_at'
    ];

    protected $casts = [
        'channel_id'          => 'integer',
        'status' => 'integer',
        'elapsed_time'        => 'integer',
        'callback_time'       => 'datetime',
        'created_at'          => 'datetime',
    ];

    // belongsTo channel
    public function channel(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ModelChannel::class, 'channel_id','id' );
    }
}
