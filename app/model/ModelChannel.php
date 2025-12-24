<?php

namespace app\model;

use app\model\lib\CustomSoftDeletes;
use Carbon\Carbon;

/**
 * @property int $id 主键
 * @property string $channel_code 渠道编码
 * @property string $channel_name 渠道名称
 * @property string $channel_icon 渠道图标
 * @property int $channel_type 渠道类型:1-银行 2-上游第三方支付
 * @property string $country_code 国家代码(IN=印度)
 * @property string $currency 默认币种
 * @property string $api_base_url API基础地址
 * @property string $doc_url 文档地址
 * @property int $support_collection 支持代收
 * @property int $support_disbursement 支持代付
 * @property mixed $config 渠道配置(JSON)
 * @property boolean $status 状态:1-启用 0-停用
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon $deleted_at
 */
final class ModelChannel extends BasicModel
{
    use CustomSoftDeletes;

    /**
     * The table associated with the model.
     * @var string
     */
    protected $table = 'channel';

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
        'channel_code',
        'channel_name',
        'channel_icon',
        'channel_type',
        'country_code',
        'currency',
        'api_base_url',
        'doc_url',
        'support_collection',
        'support_disbursement',
        'config',
        'status',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    protected $casts = [
        'id'                   => 'integer',
        'channel_code'         => 'string',
        'channel_name'         => 'string',
        'channel_icon'         => 'string',
        'channel_type'         => 'integer',
        'country_code'         => 'string',
        'currency'             => 'string',
        'api_base_url'         => 'string',
        'doc_url'              => 'string',
        'support_collection'   => 'boolean',
        'support_disbursement' => 'boolean',
        'config'               => 'array',
        'status'               => 'boolean',
        'created_at'           => 'datetime',
        'updated_at'           => 'datetime',
        'deleted_at'           => 'datetime',
    ];

}
