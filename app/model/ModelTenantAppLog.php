<?php

namespace app\model;

use Carbon\Carbon;

/**
* @property int $id 主键 主键
* @property string $tenant_id 租户编号
* @property int $app_id 接口ID
* @property string $app_key app_key
* @property string $access_path 接口访问路径
* @property string $request_id 请求id
* @property string $request_data 请求数据
* @property string $response_code 响应状态码
* @property int $response_success 状态 (1成功 2失败)
* @property string $response_message 响应信息
* @property string $response_data 响应数据
* @property string $ip 访问IP地址
* @property string $ip_location IP所属地
* @property Carbon $access_time 访问时间
* @property string $remark 备注
* @property int $duration 耗时
*/
final class ModelTenantAppLog extends BasicModel
{
    /**
     * The table associated with the model.
     * @var string
     */
    protected $table = 'tenant_app_log';

    /**
     * The primary key associated with the table.
     * @var string
     */
    protected $primaryKey = 'id';
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     * @var array
     */
    protected $fillable = [
        'tenant_id',
        'app_id',
        'app_key',
        'access_path',
        'request_id',
        'request_data',
        'response_code',
        'response_success',
        'response_message',
        'response_data',
        'ip',
        'ip_location',
        'access_time',
        'remark',
        'duration',
    ];
}
