<?php

namespace app\model;

use app\scope\TenantDataPermissionScope;
use Carbon\Carbon;
use support\Db;

/**
 * @property int $id 主键
 * @property string $platform_order_no 平台订单号
 * @property string $tenant_order_no 下游订单号
 * @property string $upstream_order_no 上游订单号
 * @property float $amount 订单金额
 * @property float $payable_amount 订单应付金额
 * @property float $paid_amount 订单实付金额
 * @property float $fixed_fee 固定手续费
 * @property float $rate_fee 费率手续费
 * @property float $rate_fee_amount 费率手续费金额
 * @property float $total_fee 总手续费
 * @property float $upstream_fee 上游手续费
 * @property float $upstream_settlement_amount 上游结算金额
 * @property float $settlement_amount 租户入账金额
 * @property int $settlement_type 入账结算类型:0-未入账 1-实付金额 2-订单金额
 * @property int $collection_type 收款类型:1-银行卡 2-UPI 3-第三方支付
 * @property int $collection_channel_id 收款渠道ID
 * @property int $channel_account_id 渠道账户ID
 * @property int $bank_account_id 银行账户ID
 * @property Carbon $pay_time 支付时间
 * @property Carbon $expire_time 订单失效时间
 * @property string $order_source 订单来源:APP-API 管理后台 导入
 * @property int $recon_type 核销类型:
 * 0-未核销
 * 1-自动核销
 * 2-人工核销
 * 3-接口核销
 * 4-机器人核销
 * @property string $notify_url 回调地址
 * @property string $notify_remark 回调原样返回
 * @property int $notify_status 通知状态:0-未通知 1-回调中 2-通知成功 3-通知失败
 * @property string $pay_url 收银台地址
 * @property string $return_url 支付成功后跳转地址
 * @property string $tenant_id 租户编号
 * @property int $app_id 应用ID
 * @property string $payer_name 付款方名称
 * @property string $payer_account 付款账号
 * @property string $payer_bank 付款方银行
 * @property string $payer_ifsc 付款方IFSC代码
 * @property string $payer_upi 付款方UPI账号
 * @property string $description 订单描述
 * @property int $status 订单状态:
 * 0-创建 10-处理中 20-成功 30-挂起 40-失败
 * 41-已取消 43-已失效 44-已退款
 * @property string $channel_transaction_no 渠道交易号
 * @property string $error_code 错误代码
 * @property string $error_message 错误信息
 * @property string $request_id 关联API请求ID
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property string $payment_proof_photo 支付凭证照片
 * @property string $platform_transaction_no 平台交易流水号
 * @property string $utr utr
 * @property string $customer_submitted_utr 客户提交的UTR
 * @property int $settlement_delay_mode 入账类型(1:D0 2:D1 3:T0)
 * @property int $settlement_delay_days 入账延迟天数（自然日）
 * @property int $transaction_voucher_id 核销凭证id
 * @property Carbon $cancelled_at 取消时间
 * @property int $cancelled_by 取消管理员
 * @property int $customer_cancelled_by 取消客户
 * @property int $customer_created_by 创建客户
 */
final class ModelCollectionOrder extends BasicModel
{
    /**
     * The table associated with the model.
     * @var string
     */
    protected $table = 'collection_order';

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
        'platform_order_no',
        'tenant_order_no',
        'upstream_order_no',
        'amount',
        'payable_amount',
        'paid_amount',
        'fixed_fee',
        'rate_fee',
        'rate_fee_amount',
        'total_fee',
        'upstream_fee',
        'upstream_settlement_amount',
        'settlement_amount',
        'settlement_type',
        'collection_type',
        'collection_channel_id',
        'channel_account_id',
        'bank_account_id',
        'pay_time',
        'expire_time',
        'order_source',
        'recon_type',
        'notify_url',
        'notify_remark',
        'notify_status',
        'pay_url',
        'return_url',
        'tenant_id',
        'app_id',
        'payer_name',
        'payer_account',
        'payer_bank',
        'payer_ifsc',
        'payer_upi',
        'description',
        'status',
        'channel_transaction_no',
        'error_code',
        'error_message',
        'request_id',
        'created_at',
        'updated_at',
        'payment_proof_photo',
        'platform_transaction_no',
        'utr',
        'customer_submitted_utr',
        'settlement_delay_mode',
        'settlement_delay_days',
        'transaction_voucher_id',
        'cancelled_at',
        'cancelled_by',
        'customer_cancelled_by',
        'customer_created_by',
        'pay_time_hour',
    ];

    protected $casts = [
        'amount'                     => 'float',
        'payable_amount'             => 'float',
        'paid_amount'                => 'float',
        'fixed_fee'                  => 'float',
        'rate_fee'                   => 'float',
        'rate_fee_amount'            => 'float',
        'total_fee'                  => 'float',
        'upstream_fee'               => 'float',
        'upstream_settlement_amount' => 'float',
        'settlement_amount'          => 'float',
        'settlement_type'            => 'integer',
        'collection_type'            => 'integer',
        'collection_channel_id'      => 'integer',
        'channel_account_id'         => 'integer',
        'bank_account_id'            => 'integer',
        'recon_type'                 => 'integer',
        'notify_status'              => 'integer',
        'app_id'                     => 'integer',
        'status'                     => 'integer',
        'pay_time'                   => 'datetime',
        'expire_time'                => 'datetime',
        'created_at'                 => 'datetime',
        'updated_at'                 => 'datetime',
        'settlement_delay_mode'      => 'integer',
        'settlement_delay_days'      => 'integer',
        'transaction_voucher_id'     => 'integer',
        'cancelled_by'               => 'integer',
        'customer_cancelled_by'      => 'integer',
        'customer_created_by'        => 'integer',
        'cancelled_at'               => 'datetime',
    ];

    public static function boot()
    {
        parent::boot();
        self::creating(function (ModelCollectionOrder $model) {
            var_dump('run ModelCollectionOrder creating==');
            if (empty($model->platform_order_no)) {
                $model->platform_order_no = buildPlatformOrderNo('CO');
            }
        });
    }

    // belongsTo channel
    public function channel(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ModelChannel::class, 'collection_channel_id', 'id');
    }

    // belongsTo channel_account
    public function channel_account(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ModelChannelAccount::class, 'channel_account_id', 'id');
    }

    // belongsTo bank_account
    public function bank_account(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ModelBankAccount::class, 'bank_account_id', 'id');
    }

    // cancel_operator
    public function cancel_operator(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ModelUser::class, 'cancelled_by', 'id');
    }

    // cancel_customer
    public function cancel_customer(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ModelTenantUser::class, 'customer_cancelled_by', 'id');
    }

    // created_customer
    public function created_customer(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ModelTenantUser::class, 'customer_created_by', 'id');
    }

    public function scopeWithTenantPermission($query): \Illuminate\Database\Eloquent\Builder
    {
        return (new TenantDataPermissionScope())->apply($query, $this);
    }

    // hasMany status_records
    public function status_records(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ModelCollectionOrderStatusRecords::class, 'order_id', 'id')->orderBy('id', 'desc');
    }

    // 平台交易状态 belongsTo
    public function settlement_status(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ModelTransactionRecord::class, 'platform_transaction_no', 'transaction_no');
    }
}
