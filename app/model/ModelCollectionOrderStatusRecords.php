<?php

namespace app\model;

use Carbon\Carbon;

/**
* @property int $id 主键
* @property int $order_id 订单ID
* @property int $status 订单状态
* @property string $desc_cn 中文信息
* @property string $desc_en 英文信息
* @property Carbon $created_at 创建时间
* @property string $remark 备注
*/
final class ModelCollectionOrderStatusRecords extends BasicModel
{
    /**
     * The table associated with the model.
     * @var string
     */
    protected $table = 'collection_order_status_records';

    /**
     * The primary key associated with the table.
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;
    
    /**
     * The attributes that are mass assignable.
     * @var array
     */
    protected $fillable = [
        'order_id',
        'status',
        'desc_cn',
        'desc_en',
        'created_at',
        'remark'
    ];
}
