<?php

namespace app\model;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $user_id 用户ID
 * @property int $dept_id 部门ID
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon $deleted_at
 * @property null|ModelDepartment $department
 * @property null|ModelUser $user
 */
class ModelDeptLeader extends BasicModel
{
    use SoftDeletes;

    public $incrementing = false;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'dept_leader';

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
        'user_id',
        'dept_id',
        'created_at',
        'updated_at',
    ];

    public function department(): BelongsTo
    {
        return $this->belongsTo(ModelDepartment::class, 'dept_id', 'id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(ModelUser::class, 'user_id', 'id');
    }
}
