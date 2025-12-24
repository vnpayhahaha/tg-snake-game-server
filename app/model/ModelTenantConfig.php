<?php

namespace app\model;

use Carbon\Carbon;

/**
 * @property int $id 主键 配置ID
 * @property string $tenant_id 租户编号
 * @property string $group_code 分组编码
 * @property string $code 唯一编码
 * @property string $name 配置名称
 * @property string $content 配置内容
 * @property int $enabled 是否启用
 * @property string $intro 介绍说明
 * @property mixed $option 备用选项
 * @property int $created_by 创建者
 * @property int $updated_by 更新者
 * @property Carbon $created_at 创建时间
 * @property Carbon $updated_at 更新时间
 */
final class ModelTenantConfig extends BasicModel
{
    /**
     * The table associated with the model.
     * @var string
     */
    protected $table = 'tenant_config';

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
        'group_code',
        'code',
        'name',
        'content',
        'enabled',
        'intro',
        'option',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'id'         => 'integer',
        'tenant_id'  => 'string',
        'group_code' => 'string',
        'code'       => 'string',
        'name'       => 'string',
        'content'    => 'string',
        'enabled'    => 'boolean',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // belongsTo tenant
    public function tenant()
    {
        return $this->belongsTo(ModelTenant::class, 'tenant_id', 'tenant_id');
    }

}
