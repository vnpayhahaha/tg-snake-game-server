<?php

namespace app\model;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id 主键 主键
 * @property string $name 配置组名称
 * @property string $code 配置组标识
 * @property string $icon 配置组图标
 * @property int $created_by 创建者
 * @property int $updated_by 更新者
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property string $remark 备注
 */
final class ModelSystemConfigGroup extends BasicModel
{
    /**
     * The table associated with the model.
     * @var string
     */
    protected $table = 'system_config_group';

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
        'name',
        'code',
        'icon',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at',
        'remark'
    ];

    /**
     * The attributes that should be cast to native types.
     */
    protected $casts = [
        'id'         => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // 一对多关系，一个配置组可以有多个配置项
    public function info(): HasMany
    {
        return $this->hasMany(ModelSystemConfig::class, 'group_id'); // 'group_id' 是 Config 表中的外键
    }

}
