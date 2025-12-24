<?php

namespace app\model;

use Carbon\Carbon;

/**
* @property int $id 主键 主键
* @property string $tenant_id 租户编号
* @property string $username 用户名
* @property string $ip 登录IP地址
* @property string $os 操作系统
* @property string $browser 浏览器
* @property int $status 登录状态 (1成功 2失败)
* @property string $message 提示消息
* @property Carbon $login_time 登录时间
* @property string $remark 备注
*/
final class ModelTenantUserLoginLog extends BasicModel
{
    /**
     * The table associated with the model.
     * @var string
     */
    protected $table = 'tenant_user_login_log';

    /**
     * The primary key associated with the table.
     * @var string
     */
    protected $primaryKey = 'id';

    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     * @var array
     */
    protected $fillable = [
        'tenant_id',
        'username',
        'ip',
        'os',
        'browser',
        'status',
        'message',
        'login_time',
        'remark'
    ];

    protected $casts = [
        'id'         => 'integer',
        'status'     => 'integer',
        'login_time' => 'datetime'
    ];

    public static function boot()
    {
        parent::boot();
        ModelTenantUserLoginLog::creating(function (ModelTenantUserLoginLog $event) {
            if ($event->login_time === null) {
                $event->login_time = Carbon::now();
            }
        });
    }
}
