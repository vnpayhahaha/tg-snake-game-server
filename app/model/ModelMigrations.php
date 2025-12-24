<?php

namespace app\model;

/**
* @property int $id 主键
* @property string $migration
* @property int $batch
*/
final class ModelMigrations extends BasicModel
{
    /**
     * The table associated with the model.
     * @var string
     */
    protected $table = 'migrations';

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
        'migration',
        'batch'
    ];
}
