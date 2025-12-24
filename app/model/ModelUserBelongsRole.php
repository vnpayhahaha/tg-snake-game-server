<?php

namespace app\model;

use Carbon\Carbon;

/**
* @property int $id 主键
* @property int $user_id 用户id
* @property int $role_id 角色id
* @property Carbon $created_at
* @property Carbon $updated_at
*/
final class ModelUserBelongsRole extends BasicModel
{
    /**
     * The table associated with the model.
     * @var string
     */
    protected $table = 'user_belongs_role';

    /**
     * The primary key associated with the table.
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     * @var array
     */
    protected $fillable = [
        'id',
        'user_id',
        'role_id',
    ];
    /**
     * The attributes that should be cast to native types.
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'user_id' => 'integer',
        'role_id' => 'integer',
    ];
}
