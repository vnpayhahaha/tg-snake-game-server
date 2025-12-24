<?php

namespace app\model;

use app\model\lib\CustomSoftDeletes;
use Carbon\Carbon;

/**
 * @property int $id 主键
 * @property int $channel_id 渠道ID
 * @property string $merchant_id 渠道商户ID
 * @property array $api_config API配置 {
 * "api_key": "KEY-12345",
 * "api_secret": "SECRET-67890",
 * "token": "TOKEN-ABCDE",
 * "app_id": "APP-123"
 * "version": "v2.1.0",          -- 接口版本
 * "encryption": "RSA",           -- 加密方式
 * "sign_algo": "SHA256",         -- 签名算法
 * "request_format": "JSON"       -- 请求格式
 * }
 * @property mixed $document_info 文档信息 {
 * "url": "https://doc.example.com/v2",
 * "access_code": "DOC-SECRET-123",
 * }
 * @property string $api_version 接口版本
 * @property string $callback_url 回调地址
 * @property string $ip_whitelist 回调请求IP白名单
 * @property float $balance 渠道账户余额
 * @property string $currency 币种
 * @property float $used_quota 实际已用金额额度
 * @property float $limit_quota 限制使用金额额度
 * @property int $today_receipt_count 当日已收款次数
 * @property int $today_payment_count 当日已付款次数
 * @property float $today_receipt_amount 当日已收款金额
 * @property float $today_payment_amount 当日已付款金额
 * @property Carbon $stat_date 统计日期(YYYY-MM-DD)
 * @property boolean $status 状态:1-启用 0-停用
 * @property int $support_collection 支持代收
 * @property int $support_disbursement 支持代付
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon $deleted_at
 * @property float $daily_max_receipt 单日最大收款限额
 * @property float $daily_max_payment 单日最大付款限额
 * @property int $daily_max_receipt_count 单日最大收款次数
 * @property int $daily_max_payment_count 单日最大付款次数
 * @property float $max_receipt_per_txn 单笔最大收款限额
 * @property float $max_payment_per_txn 单笔最大付款限额
 * @property float $min_receipt_per_txn 单笔最小收款限额
 * @property float $min_payment_per_txn 单笔最小付款限额
 */
final class ModelChannelAccount extends BasicModel
{
    use CustomSoftDeletes;

    /**
     * The table associated with the model.
     * @var string
     */
    protected $table = 'channel_account';

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
        'merchant_id',
        'api_config',
        'document_info',
        'api_version',
        'callback_url',
        'ip_whitelist',
        'balance',
        'currency',
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
        'created_at',
        'updated_at',
        'deleted_at',
        'daily_max_receipt',
        'daily_max_payment',
        'daily_max_receipt_count',
        'daily_max_payment_count',
        'max_receipt_per_txn',
        'max_payment_per_txn',
        'min_receipt_per_txn',
        'min_payment_per_txn',
    ];

    protected $casts = [
        'id'                   => 'integer',
        'channel_id'           => 'integer',
        'merchant_id'          => 'string',
        'api_config'           => 'array',
        'document_info'        => 'string',
        'api_version'          => 'string',
        'callback_url'         => 'string',
        'ip_whitelist'         => 'string',
        'balance'              => 'decimal:2',
        'currency'             => 'string',
        'used_quota'           => 'decimal:2',
        'limit_quota'          => 'decimal:2',
        'today_receipt_count'  => 'integer',
        'today_payment_count'  => 'integer',
        'today_receipt_amount' => 'decimal:2',
        'today_payment_amount' => 'decimal:2',
        'stat_date'            => 'string',
        'status'               => 'boolean',
        'support_collection'   => 'boolean',
        'support_disbursement' => 'boolean',
        'created_at'           => 'datetime',
        'updated_at'           => 'datetime',
        'deleted_at'           => 'datetime',
        'daily_max_receipt'       => 'decimal:2',
        'daily_max_payment'       => 'decimal:2',
        'daily_max_receipt_count' => 'integer',
        'daily_max_payment_count' => 'integer',
        'max_receipt_per_txn'     => 'decimal:2',
        'max_payment_per_txn'     => 'decimal:2',
        'min_receipt_per_txn'     => 'decimal:2',
        'min_payment_per_txn'     => 'decimal:2',
    ];

    // belongsTo channel
    public function channel()
    {
        return $this->belongsTo(ModelChannel::class, 'channel_id','id' );
    }

}
