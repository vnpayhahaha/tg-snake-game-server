<?php

namespace app\model;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
* @property int $user_id
* @property int $dept_id
* @property Carbon $created_at
* @property Carbon $updated_at
* @property Carbon $deleted_at
*/
final class ModelUserDept extends BasicModel
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     * @var string
     */
    protected $table = 'user_dept';

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
        'user_id',
        'dept_id',
        'created_at',
        'updated_at',
        'deleted_at'
    ];
}
