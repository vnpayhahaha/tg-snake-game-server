<?php

namespace app\model;

use app\lib\JwtAuth\user\AuthorizationUserInterface;
use app\model\enums\TenantUserStatus;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;
use function Aws\boolean_value;

/**
 * @property int $id 主键 用户ID
 * @property string $tenant_id 租户编号
 * @property string $username 用户名
 * @property string $password 密码
 * @property string $phone 手机号码
 * @property string $avatar 头像
 * @property string $last_login_ip 最后登陆IP
 * @property Carbon $last_login_time 最后登陆时间
 * @property boolean $status 状态(1正常 2停用)
 * @property boolean $is_enabled_google google验证(1正常 2停用)
 * @property string $google_secret Google验证密钥
 * @property boolean $is_bind_google 是否已绑定Google验证(1yes 2no)
 * @property int $created_by 创建者
 * @property Carbon $created_at 创建时间
 * @property int $updated_by 更新者
 * @property Carbon $updated_at 更新时间
 * @property int $deleted_by 删除者
 * @property Carbon $deleted_at 删除时间
 * @property string $ip_whitelist IP白名单
 * @property string $remark 备注
 * @property array $backend_setting 后台设置数据
 */
final class ModelTenantUser extends BasicModel implements AuthorizationUserInterface
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     * @var string
     */
    protected $table = 'tenant_user';

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
        'id',
        'tenant_id',
        'username',
        'password',
        'phone',
        'avatar',
        'last_login_ip',
        'last_login_time',
        'status',
        'is_enabled_google',
        'google_secret',
        'is_bind_google',
        'created_by',
        'created_at',
        'updated_by',
        'updated_at',
        'deleted_by',
        'deleted_at',
        'ip_whitelist',
        'remark',
        'backend_setting'
    ];

    protected $casts = [
        'id'                => 'integer',
        'last_login_time'   => 'datetime',
        'status'            => 'boolean',
        'is_enabled_google' => 'boolean',
        'is_bind_google'    => 'boolean',
        'created_by'        => 'integer',
        'created_at'        => 'datetime',
        'updated_by'        => 'integer',
        'updated_at'        => 'datetime',
        'deleted_by'        => 'integer',
        'deleted_at'        => 'datetime',
        'backend_setting'   => 'json',
    ];

    public function getUserById($id)
    {
        return $this->with('tenant_account:id,tenant_id,balance_available,balance_frozen,account_type')->where('id', $id)->first();
    }

    public function setPasswordAttribute($value): void
    {
        $this->attributes['password'] = password_hash((string)$value, \PASSWORD_DEFAULT);
    }

    public function verifyPassword(string $password): bool
    {
        return password_verify($password, $this->password);
    }

    public function resetPassword(): void
    {
        var_dump('---setPasswordAttribute--');
        $this->password = 123456;
    }

    // belongsTo tenant
    public function tenant()
    {
        return $this->belongsTo(ModelTenant::class, 'tenant_id', 'tenant_id');
    }

    // tenant_accounts
    public function tenant_account(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ModelTenantAccount::class, 'tenant_id', 'tenant_id');
    }
}
