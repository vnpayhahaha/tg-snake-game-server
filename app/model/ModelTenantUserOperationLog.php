<?php

namespace app\model;

use Carbon\Carbon;

/**
* @property int $id 主键
* @property string $tenant_id 租户编号
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
* @property int $is_success 操作是否成功(1:成功,0:失败)
* @property string $response_data 响应数据
* @property int $operator_id 操作者ID
* @property string $request_id uuid
* @property int $request_duration 请求耗时(毫秒)
*/
final class ModelTenantUserOperationLog extends BasicModel
{
    /**
     * The table associated with the model.
     * @var string
     */
    protected $table = 'tenant_user_operation_log';

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
        'tenant_id',
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
        'request_duration'
    ];
}
