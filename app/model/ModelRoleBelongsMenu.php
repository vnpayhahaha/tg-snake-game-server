<?php

namespace app\model;

/**
* @property int $id 主键
* @property int $role_id 角色id
* @property int $menu_id 菜单id
* @property \Carbon\Carbon $created_at
* @property \Carbon\Carbon $updated_at
*/
final class ModelRoleBelongsMenu extends BasicModel
{
    /**
     * The table associated with the model.
     * @var string
     */
    protected $table = 'role_belongs_menu';

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
        'role_id',
        'menu_id',
        'created_at',
        'updated_at'
    ];
}
