<?php

namespace app\model;

use Carbon\Carbon;

/**
 * @property int $id 主键
 * @property string $api_name 接口名称
 * @property string $api_uri 接口URI
 * @property string $http_method 请求方式:GET,POST
 * @property array $request_params 请求参数说明
 * @property array $request_params_en 英文请求参数说明
 * @property mixed $request_example 请求参数示例
 * @property mixed $request_example_en 英文请求参数示例
 * @property array $response_params 响应参数说明
 * @property array $response_params_en 英文响应参数说明
 * @property mixed $response_example 响应参数示例
 * @property mixed $response_example_en 英文响应参数示例
 * @property string $description 接口描述
 * @property string $description_en 接口英文描述
 * @property int $status 状态:1-启用 0-停用
 * @property int $rate_limit 每秒请求限制
 * @property int $auth_mode 认证模式 (0不需要认证 1简易签名 2复杂)
 * @property int $created_by 创建人
 * @property int $updated_by 更新人
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
final class ModelTenantApiInterface extends BasicModel
{
    /**
     * The table associated with the model.
     * @var string
     */
    protected $table = 'tenant_api_interface';

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
        'api_name',
        'api_uri',
        'http_method',
        'request_params',
        'request_params_en',
        'request_example',
        'request_example_en',
        'response_params',
        'response_params_en',
        'response_example',
        'response_example_en',
        'description',
        'description_en',
        'status',
        'rate_limit',
        'auth_mode',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'status'              => 'boolean',
        'rate_limit'          => 'integer',
        'auth_mode'           => 'integer',
        'created_by'          => 'integer',
        'updated_by'          => 'integer',
        'created_at'          => 'datetime',
        'updated_at'          => 'datetime',
//        'request_params'      => 'json',
//        'request_params_en'   => 'json',
//        'request_example'     => 'json',
//        'request_example_en'  => 'json',
//        'response_params'     => 'json',
//        'response_params_en'  => 'json',
//        'response_example'    => 'json',
//        'response_example_en' => 'json',
    ];
}
