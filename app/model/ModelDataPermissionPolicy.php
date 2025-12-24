<?php

namespace app\model;

use app\model\enums\ScopeType;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

/**
 * @property int $id
 * @property int $user_id 用户ID（与角色二选一）
 * @property int $position_id 岗位ID（与用户二选一）
 * @property string $policy_type 策略类型（DEPT_SELF, DEPT_TREE, ALL, SELF, CUSTOM_DEPT, CUSTOM_FUNC）
 * @property bool $is_default 是否默认策略（默认值：true）
 * @property array $value 策略值
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon $deleted_at
 * @property Collection<int,ModelPosition>|ModelPosition[] $positions
 * @property Collection<int,ModelUser>|ModelUser[] $users
 */
class ModelDataPermissionPolicy extends BasicModel
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'data_permission_policy';

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
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'id',
        'user_id',
        'position_id',
        'policy_type',
        'is_default',
        'value',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $casts = [
        'id'          => 'integer',
        'user_id'     => 'integer',
        'position_id' => 'integer',
        'policy_type' => ScopeType::class,
        'is_default'  => 'boolean',
        'value'       => 'array',
        'created_at'  => 'datetime',
        'updated_at'  => 'datetime',
        'deleted_at'  => 'datetime',
    ];

    public function positions(): BelongsToMany
    {
        return $this->belongsToMany(ModelPosition::class, 'data_permission_policy_position', 'policy_id', 'position_id');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(ModelUser::class, 'data_permission_policy_user', 'policy_id', 'user_id');
    }
}
