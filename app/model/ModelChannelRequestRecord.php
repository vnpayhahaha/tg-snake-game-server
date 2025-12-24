<?php

namespace app\model;

use Carbon\Carbon;

/**
 * @property int $id 主键 主键ID
 * @property string $request_id 请求唯一标识
 * @property int $channel_id 渠道ID
 * @property string $api_method 调用的API方法说明
 * @property string $request_url 完整请求地址
 * @property string $http_method HTTP请求方法(GET/POST/PUT/DELETE等)
 * @property string $request_params 请求参数(JSON格式)
 * @property string $request_headers 请求头信息(JSON格式)
 * @property string $request_body 请求体内容
 * @property Carbon $request_time 请求时间
 * @property int $http_status_code HTTP响应状态码
 * @property string $response_status 业务响应状态(如渠道返回的status/code字段)
 * @property string $response_headers 响应头信息(JSON格式)
 * @property string $response_body 响应体内容
 * @property string $error_message 错误信息
 * @property Carbon $response_time 响应时间
 * @property int $elapsed_time 耗时(毫秒)
 * @property Carbon $created_at 创建时间
 * @property Carbon $updated_at 更新时间
 */
final class ModelChannelRequestRecord extends BasicModel
{
    /**
     * The table associated with the model.
     * @var string
     */
    protected $table = 'channel_request_record';

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
        'channel_id',
        'api_method',
        'request_url',
        'http_method',
        'request_params',
        'request_headers',
        'request_body',
        'request_time',
        'http_status_code',
        'response_status',
        'response_headers',
        'response_body',
        'error_message',
        'response_time',
        'elapsed_time',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'channel_id'       => 'integer',
        'http_status_code' => 'integer',
        'elapsed_time'     => 'integer',
        'request_time'     => 'datetime',
        'response_time'    => 'datetime',
        'created_at'       => 'datetime',
        'updated_at'       => 'datetime',
    ];

    // belongsTo channel
    public function channel(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ModelChannel::class, 'channel_id','id' );
    }
}
