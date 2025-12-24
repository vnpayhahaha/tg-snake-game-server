<?php

namespace app\model;

use app\constants\TransactionRecord;
use Carbon\Carbon;
use support\Db;
use Webman\Event\Event;

/**
 * @property int $id 主键 主键ID
 * @property string $transaction_no 全局唯一交易流水号
 * @property int $tenant_account_id 关联租户账户ID
 * @property string $account_id 冗余账号ID
 * @property string $tenant_id 冗余租户编号
 * @property float $amount 交易金额（正：收入，负：支出）
 * @property float $fee_amount 手续费金额
 * @property float $net_amount 净额（计算列）
 * @property int $account_type 账户变动类型（继承tenant_account类型）
 * @property int $transaction_type 业务交易类型：# 基础交易类型 (1XX)
 * 100: 收款
 * 110: 付款
 *
 * # 退款相关 (2XX)
 * 200: 收款退款
 * 210: 付款退款
 *
 *
 * # 资金调整 (4XX)
 * 400: 资金调增（人工）
 * 410: 资金调减（人工）
 * 420: 冻结资金
 * 430: 解冻资金
 * 440: 收转付
 * 450: 付转收
 *
 * # 特殊交易 (9XX)
 * 900: 冲正交易
 * 910: 差错调整
 * @property int $settlement_delay_mode 延迟模式:1-D0(立即) 2-D(自然日) 3-T(工作日)
 * @property Carbon $expected_settlement_time 预计结算时间
 * @property int $settlement_delay_days 延迟天数
 * @property int $holiday_adjustment 节假日调整:0-不调整 1-顺延 2-提前
 * @property Carbon $actual_settlement_time 实际结算时间
 * @property string $counterparty 交易对手方标识
 * @property string $order_no 关联业务订单号
 * @property int $order_id 关联业务订单ID
 * @property string $ref_transaction_no 关联原交易流水号
 * @property int $transaction_status 交易状态:0-等待结算 1-处理中 2-撤销 3-成功 4-失败
 * @property string $failed_msg 失败错误信息
 * @property string $remark 交易备注
 * @property Carbon $created_at 创建时间
 * @property Carbon $updated_at 更新时间
 */
final class ModelTransactionRecord extends BasicModel
{
    /**
     * The table associated with the model.
     * @var string
     */
    protected $table = 'transaction_record';

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
        'transaction_no',
        'tenant_account_id',
        'account_id',
        'tenant_id',
        'amount',
        'fee_amount',
        'net_amount',
        'account_type',
        'transaction_type',
        'settlement_delay_mode',
        'expected_settlement_time',
        'settlement_delay_days',
        'holiday_adjustment',
        'actual_settlement_time',
        'counterparty',
        'order_no',
        'order_id',
        'ref_transaction_no',
        'transaction_status',
        'failed_msg',
        'remark',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'id'                       => 'string',
        'transaction_no'           => 'string',
        'tenant_account_id'        => 'string',
        'account_id'               => 'string',
        'tenant_id'                => 'string',
        'amount'                   => 'decimal:2',
        'fee_amount'               => 'decimal:2',
        'net_amount'               => 'decimal:2',
        'account_type'             => 'integer',
        'transaction_type'         => 'integer',
        'settlement_delay_mode'    => 'integer',
        'expected_settlement_time' => 'datetime',
        'settlement_delay_days'    => 'integer',
        'holiday_adjustment'       => 'integer',
        'actual_settlement_time'   => 'datetime',
        'counterparty'             => 'string',
        'order_no'                 => 'string',
        'order_id'                 => 'integer',
        'ref_transaction_no'       => 'string',
        'transaction_status'       => 'integer',
        'remark'                   => 'string',
        'created_at'               => 'datetime',
        'updated_at'               => 'datetime',
    ];

    public static function boot()
    {
        parent::boot();
        self::creating(static function (ModelTransactionRecord $model) {
            if (empty($model->transaction_no)) {
                // 设置 transaction_no
                $milliseconds = (int)(microtime(true) * 1000);
                $random = substr(md5(uniqid('', true)), 0, 3); // 3位随机数
                $model->transaction_no = 'TN' . date('YmdHis') . $random . substr($milliseconds, -3);
            }
        });

//        self::created(static function (ModelTransactionRecord $model) {
//            Event::dispatch('app.transaction.created', $model);
//        });

    }
}
