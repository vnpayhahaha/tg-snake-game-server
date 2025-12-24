<?php

namespace app\model;

use app\model\lib\CustomSoftDeletes;
use Carbon\Carbon;
use Illuminate\Support\Str;

/**
 * @property int $id 主键 主键
 * @property string $tenant_id 租户编号
 * @property string $app_name 应用名称
 * @property string $app_key 应用ID
 * @property string $app_secret 应用密钥
 * @property boolean $status 状态 (1正常 2停用)
 * @property string $description 应用介绍
 * @property int $created_by 创建者
 * @property Carbon $created_at 创建时间
 * @property int $updated_by 更新者
 * @property Carbon $updated_at 更新时间
 * @property int $deleted_by 删除者
 * @property Carbon $deleted_at 删除时间
 * @property string $remark 备注
 */
final class ModelTenantApp extends BasicModel
{
    use CustomSoftDeletes;

    /**
     * The table associated with the model.
     * @var string
     */
    protected $table = 'tenant_app';

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
        'tenant_id',
        'app_name',
        'app_key',
        'app_secret',
        'status',
        'description',
        'created_by',
        'created_at',
        'updated_by',
        'updated_at',
        'deleted_by',
        'deleted_at',
        'remark',
    ];

    protected $casts = [
        'status'                => 'boolean',
        'created_by'            => 'integer',
        'updated_by'            => 'integer',
        'deleted_by'            => 'integer',
        'created_at'            => 'datetime',
        'updated_at'            => 'datetime',
        'deleted_at'            => 'datetime',
    ];

    // belongsTo tenant
    public function tenant()
    {
        return $this->belongsTo(ModelTenant::class, 'tenant_id', 'tenant_id');
    }

    public static function boot()
    {
        parent::boot();

//        ModelTenantApp::creating(function (ModelTenantApp $model) {
//            // 随机生成16位 app_key 和 hash app_secret
//            $randomStr = Str::random(16);
//            $model->app_key = $randomStr;
//            $model->app_secret = md5($randomStr);
//        });

        self::updating(static function (ModelTenantApp $model) {
            $model->updated_by = request()->user->id ?? 0;
        });

        self::deleting(static function (ModelTenantApp $model) {
            if ($model->isForceDeleting()) {
                return; // 硬删除不记录
            }
            // 从请求或上下文获取删除者ID（示例）
            $deletedBy = request()->user->id ?? 0;
            $model->deleted_by = $deletedBy;
        });
    }
}
