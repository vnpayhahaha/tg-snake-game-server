<?php

namespace app\model;

use app\model\enums\RecycleBinEnabled;
use Carbon\Carbon;

/**
 * @property int $id 主键 ID
 * @property string $tenant_id 租户编号
 * @property string $data 回收的数据
 * @property string $table_name 数据表
 * @property string $table_prefix 表前缀
 * @property RecycleBinEnabled $enabled 是否已还原(1已还原 2未还原)
 * @property string $ip 操作者IP
 * @property int $operate_by 操作管理员
 * @property Carbon $created_at 创建时间
 * @property Carbon $updated_at 更新时间
 */
final class ModelRecycleBin extends BasicModel
{
    /**
     * The table associated with the model.
     * @var string
     */
    protected $table = 'recycle_bin';

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
        'data',
        'table_name',
        'table_prefix',
        'enabled',
        'ip',
        'operate_by',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'id'         => 'integer',
        'enabled'    => RecycleBinEnabled::class,
        'operate_by' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

}
