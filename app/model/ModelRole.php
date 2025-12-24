<?php

namespace app\model;

use app\model\enums\RoleStatus;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;

/**
 * @property int $id 主键
 * @property string $name 角色名称
 * @property string $code 角色代码
 * @property int $data_scope 数据范围（1：全部数据权限 2：自定义数据权限 3：本部门数据权限 4：本部门及以下数据权限 5：本人数据权限）
 * @property RoleStatus $status 状态 (1正常 2停用)
 * @property int $sort 排序
 * @property int $created_by 创建者
 * @property int $updated_by 更新者
 * @property Carbon $created_at 创建时间
 * @property Carbon $updated_at 更新时间
 * @property string $remark 备注
 * @property Collection|ModelMenu[] $menus
 * @property Collection|ModelUser[] $users
 */
final class ModelRole extends BasicModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'role';

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
     * @var array
     */
    protected $fillable = [
        'id',
        'name',
        'code',
        'data_scope',
        'status',
        'sort',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at',
        'remark',
    ];

    protected $casts = [
        'id'         => 'integer',
        'data_scope' => 'integer',
        'status'     => RoleStatus::class,
        'sort'       => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * 通过中间表获取菜单.
     */
    public function menus(): BelongsToMany
    {
        return $this->belongsToMany(
            ModelMenu::class,
            'role_belongs_menu',
            'role_id',
            'menu_id'
        );
    }

    public function users(): BelongsToMany
    {
        // @phpstan-ignore-next-line
        return $this->belongsToMany(
            ModelUser::class,
            // @phpstan-ignore-next-line
            'user_belongs_role',
            'role_id',
            'user_id'
        );
    }

    public static function boot()
    {
        parent::boot();
        ModelRole::deleting(function (ModelRole $role){
            $role->users()->detach();
            $role->menus()->detach();
        });
    }

}
