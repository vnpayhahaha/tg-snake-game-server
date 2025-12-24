<?php

namespace app\model;

use app\model\casts\MetaCast;
use app\model\enums\MenuStatus;
use app\model\fieldExpansion\ModelMenuMeta;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;

/**
 * @property int $id 主键
 * @property int $parent_id 父ID
 * @property string $name 菜单名称
 * @property string $component 组件路径
 * @property string $redirect 跳转地址
 * @property string $path 地址
 * @property MenuStatus $status 状态 (1正常 2停用)
 * @property ModelMenuMeta $meta 附加属性
 * @property int $sort 排序
 * @property int $created_by 创建者
 * @property int $updated_by 更新者
 * @property Carbon $created_at 创建时间
 * @property Carbon $updated_at 更新时间]
 * @property string $remark 备注
 * @property Collection|ModelRole[] $roles
 * @property Collection|ModelMenu[] $children 字段
 */
final class ModelMenu extends BasicModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'menu';

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
        'parent_id',
        'name',
        'component',
        'redirect',
        'path',
        'status',
        'meta',
        'sort',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at',
        'remark',
    ];

    /**
     * The attributes that should be cast to native types.
     */
    protected $casts = [
        'id'         => 'integer',
        'parent_id'  => 'integer',
        'status'     => MenuStatus::class,
        'sort'       => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'meta'       => MetaCast::class,
        'path'       => 'string',
    ];

    /**
     * 通过中间表获取角色.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            ModelRole::class,
            'role_belongs_menu',
            'menu_id',
            'role_id'
        );
    }

    public function children()
    {
        // @phpstan-ignore-next-line
        return $this
            ->hasMany(self::class, 'parent_id', 'id')
            ->where('status', MenuStatus::Normal)
            ->orderBy('sort')
            ->with('children');
    }
}
