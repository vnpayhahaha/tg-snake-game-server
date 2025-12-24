<?php

namespace app\model;

use Carbon\Carbon;

/**
 * @property int $id 主键
 * @property int $queue_id 队列ID
 * @property string $tenant_id 租户编号
 * @property int $app_id 应用ID
 * @property int $account_type 账户变动类型（继承tenant_account类型1-收款账户 2-付款账户）
 * @property int $collection_order_id 收款订单ID
 * @property int $disbursement_order_id 付款订单ID
 * @property int $notification_type 通知类型:1-支付结果 2-退款结果 3-账单通知
 * @property string $notification_url 通知地址
 * @property string $request_method 请求方式
 * @property string $request_data 请求数据
 * @property int $response_status 响应状态码
 * @property string $response_data 响应数据
 * @property int $execute_count 重试次数
 * @property int $status 回调状态:0-失败 1-成功
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
final class ModelTenantNotificationRecord extends BasicModel
{
    /**
     * The table associated with the model.
     * @var string
     */
    protected $table = 'tenant_notification_record';

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
        'queue_id',
        'tenant_id',
        'app_id',
        'account_type',
        'collection_order_id',
        'disbursement_order_id',
        'notification_type',
        'notification_url',
        'request_method',
        'request_data',
        'response_status',
        'response_data',
        'execute_count',
        'status',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'id'                    => 'integer',
        'queue_id'              => 'integer',
        'app_id'                => 'integer',
        'account_type'          => 'integer',
        'collection_order_id'   => 'integer',
        'disbursement_order_id' => 'integer',
        'notification_type'     => 'integer',
        'response_status'       => 'integer',
        'execute_count'         => 'integer',
        'status'                => 'integer',
        'created_at'            => 'datetime',
        'updated_at'            => 'datetime',
    ];

    // belongsTo tenant
    public function tenant(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ModelTenant::class, 'tenant_id', 'tenant_id');
    }

    // tenant_app
    public function app(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ModelTenantApp::class, 'app_id', 'id');
    }

    // collection_order
    public function collection_order(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ModelCollectionOrder::class, 'collection_order_id', 'id');
    }

    // disbursement_order
    public function disbursement_order(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ModelDisbursementOrder::class, 'disbursement_order_id', 'id');
    }
}
