<?php

namespace app\model;

use app\model\enums\TenantAccountType;
use Carbon\Carbon;

/**
 * @property int $id 主键 ID
 * @property string $tenant_id 租户编号
 * @property string $account_id 自定义账户ID（格式：租户ID_账户类型）
 * @property float $balance_available 可用余额
 * @property float $balance_frozen 冻结金额
 * @property TenantAccountType $account_type 账户类型:1-收款账户 2-付款账户
 * @property int $version 乐观锁版本
 * @property Carbon $created_at 创建时间
 * @property Carbon $updated_at 更新时间
 */
final class ModelTenantAccount extends BasicModel
{
    /**
     * The table associated with the model.
     * @var string
     */
    protected $table = 'tenant_account';

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
        'account_id',
        'balance_available',
        'balance_frozen',
        'account_type',
        'version',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'id'                => 'string',
        'tenant_id'         => 'string',
        'account_id'        => 'string',
        'balance_available' => 'decimal:2',
        'balance_frozen'    => 'decimal:2',
        'account_type'      => TenantAccountType::class,
        'version'           => 'string',
        'created_at'        => 'datetime',
        'updated_at'        => 'datetime',
    ];

    // belongsTo tenant
    public function tenant(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ModelTenant::class, 'tenant_id', 'tenant_id');
    }

    // 乐观锁更新方法
    public function updateWithLock(array $data): int
    {
        if (!isset($data['version'])) {
            throw new \InvalidArgumentException('Version field is required for optimistic lock');
        }

        $currentVersion = $data['version'];
        $data['version'] = $currentVersion + 1;

        $result = $this->newQuery()
            ->where('id', $this->id)
            ->where('version', $currentVersion)
            ->update($data);

        if ($result === 0) {
            throw new \RuntimeException('Optimistic lock failed: Version mismatch');
        }

        return $result;
    }

}
