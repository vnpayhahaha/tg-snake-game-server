<?php

namespace app\model;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

/**
 * @property int $id
 * @property string $name 岗位名称
 * @property int $dept_id 部门ID
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon $deleted_at
 * @property null|ModelDepartment $department
 * @property Collection<int,ModelUser>|ModelUser[] $users
 * @property ModelDataPermissionPolicy $policy
 */
class ModelPosition extends BasicModel
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     * @var string
     */
    protected $table = 'position';

    /**
     * The primary key associated with the table.
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
        'name',
        'dept_id',
        'created_at',
        'updated_at',
    ];

    public function department(): BelongsTo
    {
        return $this->belongsTo(ModelDepartment::class, 'dept_id', 'id');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(ModelUser::class, 'user_position', 'position_id', 'user_id');
    }

    public function policy(): HasOne
    {
        return $this->hasOne(ModelDataPermissionPolicy::class, 'position_id', 'id');
    }
}
