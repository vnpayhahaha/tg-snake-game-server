<?php

namespace app\model;

use Carbon\Carbon;

/**
 * @property int $id 主键 主键ID
 * @property string $tenant_id 租户编号
 * @property int $tenant_account_id 关联租户账户ID
 * @property int $account_type 账户类型:1-收款账户 2-付款账户
 * @property float $change_amount 变更金额（正：增加，负：减少）
 * @property float $balance_available_before 变更前余额
 * @property float $balance_available_after 变更后余额
 * @property float $balance_frozen_before 变更前冻结金额
 * @property float $balance_frozen_after 变更后冻结金额
 * @property int $change_type 变更类型：1-交易 2-人工调整 3-冲正 4-冻结/解冻 6-转入/转出
 * @property string $transaction_no 关联交易流水号
 * @property Carbon $created_at 记录创建时间
 */
final class ModelTenantAccountRecord extends BasicModel
{
    /**
     * The table associated with the model.
     * @var string
     */
    protected $table = 'tenant_account_record';

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
        'tenant_account_id',
        'account_id',
        'account_type',
        'change_amount',
        'balance_available_before',
        'balance_available_after',
        'balance_frozen_before',
        'balance_frozen_after',
        'change_type',
        'transaction_no',
        'created_at'
    ];

    protected $casts = [
        'id'                       => 'string',
        'tenant_id'                => 'string',
        'tenant_account_id'        => 'integer',
        'account_id'               => 'string',
        'account_type'             => 'integer',
        'change_amount'            => 'decimal:2',
        'balance_available_before' => 'decimal:2',
        'balance_available_after'  => 'decimal:2',
        'balance_frozen_before'    => 'decimal:2',
        'balance_frozen_after'     => 'decimal:2',
        'change_type'              => 'integer',
        'transaction_no'           => 'string',
        'created_at'               => 'datetime',
    ];

    // belongsTo tenant
    public function tenant()
    {
        return $this->belongsTo(ModelTenant::class, 'tenant_id', 'tenant_id');
    }
}
