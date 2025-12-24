<?php

namespace app\model;

use Carbon\Carbon;

/**
 * @property int $id
 * @property string $username 用户名
 * @property string $method 请求方式
 * @property string $router 请求路由
 * @property string $service_name 业务名称
 * @property string $ip 请求IP地址
 * @property Carbon $created_at 创建时间
 * @property Carbon $updated_at 更新时间
 * @property string $remark 备注
 * @property string $request_params 请求参数
 * @property int $response_status 响应状态码
 * @property int $is_success 操作是否成功(1:成功,2:失败)
 * @property string $response_data 响应数据
 * @property int $operator_id 操作者ID
 * @property string $request_id uuid请求ID
 * @property int $request_duration 请求耗时(毫秒)
 */
class ModelUserOperationLog extends BasicModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user_operation_log';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'id',
        'username',
        'method',
        'router',
        'service_name',
        'ip',
        'created_at',
        'updated_at',
        'remark',
        'request_params',
        'response_status',
        'is_success',
        'response_data',
        'operator_id',
        'request_id',
        'request_duration',
    ];

    /**
     * The attributes that should be cast to native types.
     */
    protected $casts = [
        'id'               => 'integer',
        'created_by'       => 'integer',
        'updated_by'       => 'integer',
        'created_at'       => 'datetime',
        'updated_at'       => 'datetime',
        'is_success'       => 'integer',
        'response_status'  => 'integer',
        'request_duration' => 'integer',
    ];

}
