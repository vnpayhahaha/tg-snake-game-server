<?php

namespace app\model;

use app\model\lib\CustomSoftDeletes;
use Carbon\Carbon;
use support\Redis;

/**
 * @property int $id 主键
 * @property int $channel_id 银行id
 * @property string $branch_name 支行名称
 * @property string $account_holder 账户持有人
 * @property string $account_number 账号
 * @property string $ifsc_code IFSC代码
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon $deleted_at
 * @property float $balance 账户余额
 * @property int $float_amount_enabled 代收小数浮动开关:0关闭 1启用
 * @property float $daily_max_receipt 单日最大收款限额
 * @property float $daily_max_payment 单日最大付款限额
 * @property int $daily_max_receipt_count 单日最大收款次数
 * @property int $daily_max_payment_count 单日最大付款次数
 * @property float $max_receipt_per_txn 单笔最大收款限额
 * @property float $max_payment_per_txn 单笔最大付款限额
 * @property float $min_receipt_per_txn 单笔最小收款限额
 * @property float $min_payment_per_txn 单笔最小付款限额
 * @property int $security_level 安全等级(1-99)
 * @property Carbon $last_used_time 最后使用时间
 * @property string $upi_id UPI支付地址
 * @property float $used_quota 实际已用金额额度
 * @property float $limit_quota 限制使用金额额度
 * @property int $today_receipt_count 当日已收款次数
 * @property int $today_payment_count 当日已付款次数
 * @property float $today_receipt_amount 当日已收款金额
 * @property float $today_payment_amount 当日已付款金额
 * @property Carbon $stat_date 统计日期(YYYY-MM-DD)
 * @property int $status 状态(1启用 2停用)
 * @property int $support_collection 支持代收
 * @property int $support_disbursement 支持代付
 * @property array $down_bill_template_id 付款账单模板ID项
 * @property array $account_config 帐户配置项
 */
final class ModelBankAccount extends BasicModel
{
    use CustomSoftDeletes;

    /**
     * The table associated with the model.
     * @var string
     */
    protected $table = 'bank_account';

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
        'channel_id',
        'branch_name',
        'account_holder',
        'account_number',
        'ifsc_code',
        'created_at',
        'updated_at',
        'deleted_at',
        'balance',
        'float_amount_enabled',
        'daily_max_receipt',
        'daily_max_payment',
        'daily_max_receipt_count',
        'daily_max_payment_count',
        'max_receipt_per_txn',
        'max_payment_per_txn',
        'min_receipt_per_txn',
        'min_payment_per_txn',
        'security_level',
        'last_used_time',
        'upi_id',
        'used_quota',
        'limit_quota',
        'today_receipt_count',
        'today_payment_count',
        'today_receipt_amount',
        'today_payment_amount',
        'stat_date',
        'status',
        'support_collection',
        'support_disbursement',
        'down_bill_template_id',
        'account_config',
    ];

    protected $casts = [
        'id'                      => 'integer',
        'channel_id'              => 'integer',
        'branch_name'             => 'string',
        'account_holder'          => 'string',
        'account_number'          => 'string',
        'bank_code'               => 'string',
        'created_at'              => 'datetime',
        'updated_at'              => 'datetime',
        'deleted_at'              => 'datetime',
        'balance'                 => 'string',
        'float_amount_enabled'    => 'boolean',
        'daily_max_receipt'       => 'decimal:2',
        'daily_max_payment'       => 'decimal:2',
        'daily_max_receipt_count' => 'integer',
        'daily_max_payment_count' => 'integer',
        'max_receipt_per_txn'     => 'decimal:2',
        'max_payment_per_txn'     => 'decimal:2',
        'min_receipt_per_txn'     => 'decimal:2',
        'min_payment_per_txn'     => 'decimal:2',
        'security_level'          => 'integer',
        'last_used_time'          => 'datetime',
        'upi_id'                  => 'string',
        'used_quota'              => 'decimal:2',
        'limit_quota'             => 'decimal:2',
        'today_receipt_count'     => 'integer',
        'today_payment_count'     => 'integer',
        'today_receipt_amount'    => 'decimal:2',
        'today_payment_amount'    => 'decimal:2',
        'stat_date'               => 'string',
        'status'                  => 'boolean',
        'support_collection'      => 'boolean',
        'support_disbursement'    => 'boolean',
        'down_bill_template_id'   => 'array',
        'account_config'          => 'array',
    ];

    // belongsTo channel
    public function channel()
    {
        return $this->belongsTo(ModelChannel::class, 'channel_id', 'id');
    }

    public static function boot(): void
    {
        parent::boot();
        self::created(static function (ModelBankAccount $model) {

            $accountInfo = ModelBankAccount::query()->with('channel:id,channel_code')->find($model->id);
            Redis::connection('synchronize')->set('Model:BankAccount:' . $model->id, json_encode($accountInfo->toArray(), JSON_THROW_ON_ERROR));
        });
        self::updated(static function (ModelBankAccount $model) {

            $accountInfo = ModelBankAccount::query()->with('channel:id,channel_code')->find($model->id);
            Redis::connection('synchronize')->set('Model:BankAccount:' . $model->id, json_encode($accountInfo->toArray(), JSON_THROW_ON_ERROR));
        });
    }
}
