<?php

namespace app\model;

use app\scope\TenantDataPermissionScope;
use Carbon\Carbon;

/**
 * @property int $id 主键
 * @property string $platform_order_no 平台订单号
 * @property string $tenant_order_no 下游订单号
 * @property string $upstream_order_no 上游订单号
 * @property Carbon $pay_time 支付时间
 * @property string $order_source 订单来源:App-API 管理后台 导入
 * @property int $disbursement_channel_id 代付渠道D
 * @property int $channel_type 渠道类型：1-银行 2-上游第三方
 * @property int $bank_account_id 代付银行卡ID
 * @property int $channel_account_id 代付渠道ID
 * @property float $amount 订单金额
 * @property float $fixed_fee 固定手续费
 * @property float $rate_fee 费率手续费
 * @property float $rate_fee_amount 费率手续费金额
 * @property float $total_fee 总手续费
 * @property float $settlement_amount 租户入账金额
 * @property float $upstream_fee 上游手续费
 * @property float $upstream_settlement_amount 上游结算金额
 * @property int $payment_type 付款类型:1-银行卡 2-UPI
 * @property string $payee_bank_name 收款人银行名称
 * @property string $payee_bank_code 收款人银行编码
 * @property string $payee_account_name 收款人账户姓名
 * @property string $payee_account_no 收款人银行卡号
 * @property string $payee_upi 收款人UPI账号
 * @property string $payee_phone 收款人电话号码
 * @property string $utr 实际交易的凭证/UTR
 * @property string $tenant_id 租户编号
 * @property int $app_id 应用ID
 * @property string $description 订单描述
 * @property int $status 订单状态:
 * 0-创建中 10-待支付 11-待回填 20-成功 30-挂起
 * 40-失败 41-已取消 43-已失效 44-冲正
 * @property Carbon $expire_time 订单失效时间
 * @property string $notify_url 回调地址
 * @property string $notify_remark 回调原样返回
 * @property int $notify_status 通知状态:0-未通知 1-回调中 2-通知成功 3-通知失败
 * @property string $channel_transaction_no 渠道交易号
 * @property string $error_code 错误代码
 * @property string $error_message 错误信息
 * @property string $request_id 关联API请求ID
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon $cancelled_at 取消时间
 * @property int $cancelled_by 取消时间管理员
 * @property int $transaction_voucher_id 核销凭证id
 * @property string $down_bill_template_id 付款账单模板
 * @property int $bank_disbursement_download_id 银行支付账单下载ID
 * @property int $customer_created_by 客户端创建ID
 * @property int $customer_cancelled_by 客户端取消ID
 * @property int $transaction_record_id 交易ID
 * @property Carbon $refund_at 退款时间
 * @property string $refund_reason 退款原因
 * @property string $payment_voucher_image 支付凭证图片
 * @property string $platform_transaction_no 平台交易流水号
 */
final class ModelDisbursementOrder extends BasicModel
{
    /**
     * The table associated with the model.
     * @var string
     */
    protected $table = 'disbursement_order';

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
        'pay_time',
        'order_source',
        'disbursement_channel_id',
        'channel_type',
        'bank_account_id',
        'channel_account_id',
        'amount',
        'fixed_fee',
        'rate_fee',
        'rate_fee_amount',
        'total_fee',
        'settlement_amount',
        'upstream_fee',
        'upstream_settlement_amount',
        'payment_type',
        'payee_bank_name',
        'payee_bank_code',
        'payee_account_name',
        'payee_account_no',
        'payee_phone',
        'payee_upi',
        'utr',
        'tenant_id',
        'app_id',
        'description',
        'status',
        'expire_time',
        'notify_url',
        'notify_status',
        'notify_remark',
        'channel_transaction_no',
        'error_code',
        'error_message',
        'request_id',
        'created_at',
        'updated_at',
        'cancelled_at',
        'cancelled_by',
        'transaction_voucher_id',
        'down_bill_template_id',
        'bank_disbursement_download_id',
        'customer_created_by',
        'customer_cancelled_by',
        'transaction_record_id',
        'refund_at',
        'refund_reason',
        'payment_voucher_image',
        'platform_transaction_no',
    ];

    protected $casts = [
        'pay_time'                      => 'datetime',
        'disbursement_channel_id'       => 'integer',
        'channel_type'                  => 'integer',
        'bank_account_id'               => 'integer',
        'channel_account_id'            => 'integer',
        'amount'                        => 'float',
        'fixed_fee'                     => 'float',
        'rate_fee'                      => 'float',
        'rate_fee_amount'               => 'float',
        'total_fee'                     => 'float',
        'settlement_amount'             => 'float',
        'upstream_fee'                  => 'float',
        'upstream_settlement_amount'    => 'float',
        'payment_type'                  => 'integer',
        'app_id'                        => 'integer',
        'status'                        => 'integer',
        'expire_time'                   => 'datetime',
        'notify_status'                 => 'integer',
        'created_at'                    => 'datetime',
        'updated_at'                    => 'datetime',
        'refund_at'                     => 'datetime',
        'cancelled_at'                  => 'datetime',
        'cancelled_by'                  => 'integer',
        'transaction_voucher_id'        => 'integer',
        'bank_disbursement_download_id' => 'integer',
        'customer_created_by'           => 'integer',
        'customer_cancelled_by'         => 'integer',
        'transaction_record_id'         => 'integer',
    ];

    public static function boot()
    {
        parent::boot();
        self::creating(static function (ModelDisbursementOrder $model) {
            var_dump('run ModelCollectionOrder creating==');
            if (empty($model->platform_order_no)) {
                $model->platform_order_no = buildPlatformOrderNo('DO');
            }
        });
    }


    // belongsTo channel
    public function channel(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ModelChannel::class, 'disbursement_channel_id', 'id');
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

    // belongsTo ModelBankDisbursementDownload
    public function bank_disbursement_download(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ModelBankDisbursementDownload::class, 'bank_disbursement_download_id', 'id');
    }

    // cancel_customer
    public function cancel_customer(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ModelTenantUser::class, 'customer_cancelled_by', 'id');
    }

    public function created_customer(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ModelTenantUser::class, 'customer_created_by', 'id');
    }

    // transaction_record_id
    public function transaction_record(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ModelTransactionRecord::class, 'transaction_record_id', 'id');
    }

    public function scopeWithTenantPermission($query): \Illuminate\Database\Eloquent\Builder
    {
        return (new TenantDataPermissionScope())->apply($query, $this);
    }

    // hasMany status_records
    public function status_records(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ModelDisbursementOrderStatusRecords::class, 'order_id', 'id')->orderBy('id', 'desc');
    }

    // 平台交易状态 belongsTo
    public function settlement_status(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ModelTransactionRecord::class, 'platform_transaction_no', 'transaction_no');
    }
}
