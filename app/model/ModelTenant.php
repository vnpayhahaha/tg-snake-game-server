<?php

namespace app\model;

use app\model\lib\CustomSoftDeletes;
use Carbon\Carbon;
use support\Db;
use Webman\Event\Event;

/**
 * @property int $id 主键 id
 * @property string $tenant_id 租户编号
 * @property string $contact_user_name 联系人
 * @property string $contact_phone 联系电话
 * @property string $company_name 企业名称
 * @property string $license_number 企业代码
 * @property string $address 地址
 * @property string $intro 企业简介
 * @property string $domain 域名
 * @property int $user_num_limit 用户数量（-1不限制）
 * @property int $app_num_limit 应用数量（-1不限制）
 * @property int $is_enabled 接单启用状态(1正常 0停用)
 * @property int $created_by 创建管理员
 * @property Carbon $created_at 创建时间
 * @property Carbon $expired_at 过期时间
 * @property int $updated_by 更新者
 * @property Carbon $updated_at 更新时间
 * @property int $safe_level 安全等级(0-99)
 * @property int $deleted_by 删除者
 * @property Carbon $deleted_at 删除时间
 * @property string $remark 备注
 * @property int $settlement_delay_mode 入账类型(1:D0 2:D 3:T)
 * @property int $settlement_delay_days 入账延迟天数
 * @property int $auto_transfer 自动划扣(1是 0否)
 * @property array $receipt_fee_type 收款手续费类型(1固定 2费率)
 * @property float $receipt_fixed_fee 收款固定手续费金额
 * @property float $receipt_fee_rate 收款手续费费率(%)
 * @property array $payment_fee_type 付款手续费类型(1固定 2费率)
 * @property float $payment_fixed_fee 付款固定手续费金额
 * @property float $payment_fee_rate 付款手续费费率(%)
 * @property bool $is_receipt 是否收款(1是 0否)
 * @property bool $is_payment 是否付款(1是 0否)
 * @property float $receipt_min_amount 单次收款最小金额
 * @property float $receipt_max_amount 单次收款最大金额
 * @property float $payment_min_amount 单次付款最小金额
 * @property float $payment_max_amount 单次付款最大金额
 * @property int $receipt_settlement_type 收款结算方式(1实际金额 2订单金额)
 * @property int $upstream_enabled 启用上游第三方收款(1是 0否)
 * @property array $upstream_items 上游第三方收款顺序
 * @property boolean $float_enabled 启用金额浮动(1是 0否)
 * @property array $float_range 金额浮动区间(格式：-5,5)
 * @property array $notify_range 下游通知金额区间(格式：100,1000)
 * @property boolean $auto_assign_enabled 启用自动分配(1是 0否)
 * @property int $receipt_expire_minutes 收款订单失效时间(分钟)
 * @property int $payment_expire_minutes 付款订单失效时间(分钟)
 * @property int $reconcile_retain_minutes 金额对账保留(分钟)
 * @property int $bill_delay_minutes 账单待处理延时(分钟)
 * @property int $card_acquire_type 银行卡获取方式(1随机 2依次 3轮询)
 * @property float $auto_verify_fail_rate 自动核销失败比例(%)
 * @property array $payment_assign_items 付款分配项(JSON格式)
 * @property array $collection_use_method 收款使用方法1公户 2上游
 * @property int $tg_chat_id telegram bot chat id
 * @property int $cashier_template 收银台模板
 */
final class ModelTenant extends BasicModel
{
    use CustomSoftDeletes;

    /**
     * The table associated with the model.
     * @var string
     */
    protected $table = 'tenant';

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
        'contact_user_name',
        'contact_phone',
        'company_name',
        'license_number',
        'address',
        'intro',
        'domain',
        'user_num_limit',
        'app_num_limit',
        'is_enabled',
        'created_by',
        'created_at',
        'expired_at',
        'updated_by',
        'updated_at',
        'safe_level',
        'deleted_by',
        'deleted_at',
        'remark',
        'settlement_delay_mode',
        'settlement_delay_days',
        'auto_transfer',
        'receipt_fee_type',
        'receipt_fixed_fee',
        'receipt_fee_rate',
        'payment_fee_type',
        'payment_fixed_fee',
        'payment_fee_rate',
        'is_receipt',
        'is_payment',
        'receipt_min_amount',
        'receipt_max_amount',
        'payment_min_amount',
        'payment_max_amount',
        'receipt_settlement_type',
        'upstream_enabled',
        'upstream_items',
        'float_enabled',
        'float_range',
        'notify_range',
        'auto_assign_enabled',
        'receipt_expire_minutes',
        'payment_expire_minutes',
        'reconcile_retain_minutes',
        'bill_delay_minutes',
        'card_acquire_type',
        'auto_verify_fail_rate',
        'payment_assign_items',
        'collection_use_method',
        'tg_chat_id',
        'cashier_template',
    ];

    protected $casts = [
        'id'                       => 'integer',
        'account_count'            => 'integer',
        'is_enabled'               => 'boolean',
        'user_num_limit'           => 'integer',
        'app_num_limit'            => 'integer',
        'created_by'               => 'integer',
        'created_at'               => 'datetime',
        'updated_at'               => 'datetime',
        'expired_at'               => 'datetime',
        'deleted_at'               => 'datetime',
        'auto_transfer'            => 'boolean',
        'settlement_delay_mode'    => 'integer',
        'settlement_delay_days'    => 'integer',
        'receipt_fee_type'         => 'array',
        'receipt_fixed_fee'        => 'float',
        'receipt_fee_rate'         => 'float',
        'payment_fee_type'         => 'array',
        'payment_fixed_fee'        => 'float',
        'payment_fee_rate'         => 'float',
        'is_receipt'               => 'boolean',
        'is_payment'               => 'boolean',
        'receipt_min_amount'       => 'float',
        'receipt_max_amount'       => 'float',
        'payment_min_amount'       => 'float',
        'payment_max_amount'       => 'float',
        'receipt_settlement_type'  => 'integer',
        'upstream_enabled'         => 'boolean',
        'upstream_items'           => 'array',
        'float_enabled'            => 'boolean',
        'auto_assign_enabled'      => 'boolean',
        'receipt_expire_minutes'   => 'integer',
        'payment_expire_minutes'   => 'integer',
        'reconcile_retain_minutes' => 'integer',
        'bill_delay_minutes'       => 'integer',
        'card_acquire_type'        => 'integer',
        'auto_verify_fail_rate'    => 'float',
        'payment_assign_items'     => 'array',
        'float_range'              => 'array',
        'notify_range'             => 'array',
        'collection_use_method'    => 'array',
        'tg_chat_id'               => 'integer',
        'cashier_template'         => 'integer',
    ];

    public static function boot()
    {
        parent::boot();
        self::creating(static function (ModelTenant $model) {
            var_dump('run  creating==');
            if (empty($model->tenant_id)) {
                // 获取当前最大ID
                $maxId = Db::table($model->table)
                    ->max(Db::raw('CAST(tenant_id AS UNSIGNED)'));

                $nextId = $maxId + 1;
                $model->tenant_id = str_pad($nextId, 6, '0', STR_PAD_LEFT);
            }
        });

        self::created(static function (ModelTenant $model) {
            Event::dispatch('app.tenant.created', $model);
        });

        self::updating(static function (ModelTenant $model) {
            $model->updated_by = request()->user->id ?? 0;
        });

        self::deleting(static function (ModelTenant $model) {
            if ($model->isForceDeleting()) {
                return; // 硬删除不记录
            }
            // 从请求或上下文获取删除者ID（示例）
            $deletedBy = request()->user->id ?? 0;
            $model->deleted_by = $deletedBy;
        });
    }

    // hasMany accounts
    public function accounts(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ModelTenantAccount::class, 'tenant_id', 'tenant_id');
    }
}
