<?php

namespace app\model;

use Carbon\Carbon;

/**
* @property int $id 主键
* @property string $ptype
* @property string $v0
* @property string $v1
* @property string $v2
* @property string $v3
* @property string $v4
* @property string $v5
* @property Carbon $created_at
* @property Carbon $updated_at
*/
final class ModelRules extends BasicModel
{
    /**
     * The table associated with the model.
     * @var string
     */
    protected $table = 'rules';

    /**
     * The primary key associated with the table.
     * @var string
     */
    protected $primaryKey = 'id';

    public $timestamps = true;

    /**
     * The attributes that are mass assignable.
     * @var array
     */
    protected $fillable = [
        'ptype',
        'v0',
        'v1',
        'v2',
        'v3',
        'v4',
        'v5',
        'created_at',
        'updated_at'
    ];
}
