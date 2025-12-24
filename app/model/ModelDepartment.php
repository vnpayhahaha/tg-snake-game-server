<?php

namespace app\model;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

/**
 * @property int $id
 * @property string $name 部门名称
 * @property int $parent_id 父级部门ID
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon $deleted_at
 * @property Collection<int,ModelPosition>|ModelPosition[] $positions 岗位
 * @property Collection<int,ModelUser>|ModelUser[] $department_users 部门用户
 * @property Collection<int,ModelUser>|ModelUser[] $leader 部门领导
 * @property Collection<int,ModelDepartment>|ModelDepartment[] $children 子部门
 */
class ModelDepartment extends BasicModel
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'department';

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
     * The attributes that are mass assignable. 字段扩展
     */
    protected $fillable = [
        'id',
        'name',
        'parent_id',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    /**
     * The attributes that should be cast to native types.
     */
    protected $casts = [
        'id'         => 'integer',
        'parent_id'  => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    public static function boot()
    {
        parent::boot();
        ModelDepartment::deleted(function (ModelDepartment $model) {
            $model->positions()->delete();
            $model->department_users()->detach();
            $model->leader()->detach();
        });
    }

    public function positions(): HasMany
    {
        return $this->hasMany(ModelPosition::class, 'dept_id', 'id');
    }

    public function department_users(): BelongsToMany
    {
        return $this->belongsToMany(ModelUser::class, 'user_dept', 'dept_id', 'user_id');
    }

    public function leader(): BelongsToMany
    {
        return $this->belongsToMany(ModelUser::class, 'dept_leader', 'dept_id', 'user_id');
    }

    public function children(): HasMany
    {
        // @phpstan-ignore-next-line
        return $this->hasMany(self::class, 'parent_id', 'id')->with(['children', 'positions']);
    }

    public function getFlatChildren(): Collection
    {
        $flat = collect();
        $this->load('children'); // 预加载子部门
        $traverse = static function ($departments) use (&$traverse, $flat) {
            foreach ($departments as $department) {
                $flat->push($department);
                if ($department->children->isNotEmpty()) {
                    $traverse($department->children);
                }
            }
        };
        $traverse($this->children);
        return $flat->prepend($this); // 包含自身
    }
}
