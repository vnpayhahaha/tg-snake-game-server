<?php

namespace app\model;

use app\constants\DisbursementOrder;
use app\constants\DisbursementOrderVerificationQueue;
use Carbon\Carbon;
use support\Log;
use Webman\RedisQueue\Redis;

/**
 * @property int $id 主键
 * @property string $platform_order_no 平台订单号
 * @property float $amount 支付金额
 * @property string $utr UTR
 * @property int $payment_status 支付状态:0未支付1支付中2支付成功3支付失败
 * @property mixed $order_data 订单数据
 * @property int $process_status 处理状态:
 * 0-待处理 1-处理中 2-成功 3-失败
 * @property int $retry_count 重试次数
 * @property Carbon $next_retry_time 下次重试时间
 * @property string $rejection_reason 拒绝原因
 * @property Carbon $created_at
 * @property Carbon $processed_at
 */
final class ModelDisbursementOrderVerificationQueue extends BasicModel
{
    /**
     * The table associated with the model.
     * @var string
     */
    protected $table = 'disbursement_order_verification_queue';

    /**
     * The primary key associated with the table.
     * @var string
     */
    protected $primaryKey = 'id';

    // updated_at = processed_at
    public const UPDATED_AT = 'processed_at';


    /**
     * The attributes that are mass assignable.
     * @var array
     */
    protected $fillable = [
        'platform_order_no',
        'amount',
        'utr',
        'order_data',
        'process_status',
        'retry_count',
        'next_retry_time',
        'rejection_reason',
        'created_at',
        'processed_at',
        'payment_status',
    ];

    protected $casts = [
        'process_status' => 'integer',
        'retry_count'    => 'integer',
        'payment_status' => 'integer',
        'order_data'     => 'json',
        'created_at'     => 'datetime',
        'processed_at'   => 'datetime',
    ];

    public static function boot(): void
    {
        parent::boot();

        self::created(static function (ModelDisbursementOrderVerificationQueue $model) {
            // 待付核销队列 DisbursementOrderVerificationQueue
            if ($model->payment_status > DisbursementOrderVerificationQueue::PAY_STATUS_PAYING) {
                var_dump('待付核销队列 DisbursementOrderVerificationQueue');
                $isPush = Redis::send(DisbursementOrder::DISBURSEMENT_ORDER_WRITE_OFF_QUEUE_NAME, [
                    'platform_order_no' => $model->platform_order_no,
                    'amount'            => $model->amount,
                    'utr'               => $model->utr,
                    'rejection_reason'  => $model->rejection_reason,
                    'payment_status'    => $model->payment_status,
                    'order_data'        => $model->order_data,
                ]);
                var_dump($isPush);
                if (!$isPush) {
                    Log::error("disbursement-order-verification Queue Status Repository => addQueue  filed");
                } else {
                    Log::info("disbursement-order-verification Queue Status Repository => addQueue  success");
                    $model->process_status = DisbursementOrderVerificationQueue::PROCESS_STATUS_PROCESSING;
                    $model->save();
                }
            }

        });
    }

}
