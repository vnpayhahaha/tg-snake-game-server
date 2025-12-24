<?php

namespace app\model;

use Carbon\Carbon;

/**
* @property int $id 主键
* @property string $platform_order_no 平台订单号
* @property int $disbursement_order_id 代付订单ID
* @property string $tenant_id 租户编号
* @property int $app_id 应用ID
* @property int $channel_account_id 渠道账号ID
* @property float $amount 订单金额
* @property string $payee_bank_name 收款人银行名称
* @property string $payee_bank_code 收款人银行编码
* @property string $payee_account_name 收款人账户姓名
* @property string $payee_account_no 收款人银行卡号
* @property string $payee_phone 收款人电话号码
* @property string $payee_upi 收款人UPI账号
* @property int $payment_type 付款类型:1-银行卡 2-UPI
* @property mixed $order_data 订单完整数据
* @property int $process_status 处理状态:0-待处理 1-处理中 2-成功 3-失败
* @property int $retry_count 重试次数
* @property int $max_retry_count 最大重试次数
* @property Carbon $next_retry_time 下次重试时间
* @property string $upstream_order_no 上游订单号
* @property string $upstream_response 上游返回数据
* @property string $error_code 错误代码
* @property string $error_message 错误信息
* @property int $lock_version 乐观锁版本号
* @property Carbon $created_at
* @property Carbon $updated_at
* @property Carbon $processed_at 处理完成时间
*/
final class ModelDisbursementOrderUpstreamCreateQueue extends BasicModel
{
    /**
     * The table associated with the model.
     * @var string
     */
    protected $table = 'disbursement_order_upstream_create_queue';

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
        'disbursement_order_id',
        'tenant_id',
        'app_id',
        'channel_account_id',
        'amount',
        'payee_bank_name',
        'payee_bank_code',
        'payee_account_name',
        'payee_account_no',
        'payee_phone',
        'payee_upi',
        'payment_type',
        'order_data',
        'process_status',
        'retry_count',
        'max_retry_count',
        'next_retry_time',
        'upstream_order_no',
        'upstream_response',
        'error_code',
        'error_message',
        'lock_version',
        'created_at',
        'updated_at',
        'processed_at'
    ];
}
