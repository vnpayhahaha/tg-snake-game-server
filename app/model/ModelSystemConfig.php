<?php

namespace app\model;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id 主键
 * @property int $group_id 组id
 * @property string $key 配置键名
 * @property string $value 配置值
 * @property string $name 配置名称
 * @property string $input_type 数据输入类型
 * @property mixed $config_select_data 配置选项数据
 * @property int $sort 排序
 * @property string $remark 备注
 * @property int $created_by 创建者
 * @property int $updated_by 更新者
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
final class ModelSystemConfig extends BasicModel
{
    /**
     * The table associated with the model.
     * @var string
     */
    protected $table = 'system_config';

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
        'group_id',
        'key',
        'value',
        'name',
        'input_type',
        'config_select_data',
        'sort',
        'remark',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'group_id'           => 'integer',
        'key'                => 'string',
        'value'              => 'string',
        'name'               => 'string',
        'input_type'         => 'string',
        'config_select_data' => 'array',
        'sort'               => 'integer',
        'remark'             => 'string',
        'created_at'         => 'datetime',
        'updated_at'         => 'datetime',
        'created_by'         => 'integer',
        'updated_by'         => 'integer',
    ];

    // 反向关联配置组
    public function group(): BelongsTo
    {
        return $this->belongsTo(ModelSystemConfigGroup::class, 'group_id');
    }
}
