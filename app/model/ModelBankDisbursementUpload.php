<?php

namespace app\model;

use app\model\enums\DisbursementOrderBillTemplate;
use Carbon\Carbon;
use Webman\Event\Event;

/**
 * @property int $id 主键 主键ID
 * @property int $attachment_id 资源ID
 * @property string $file_name 文件名
 * @property string $path 存储路径
 * @property string $hash 文件hash
 * @property string $file_size 数据大小（M）
 * @property int $record_count 条数
 * @property int $created_by 创建者
 * @property Carbon $created_at 创建时间
 * @property string $suffix 文件扩展名
 * @property int $channel_id 渠道ID
 * @property DisbursementOrderBillTemplate $upload_bill_template_id 上传模板IDi
 * @property int $parsing_status 解析状态：0失败，1成功
 * @property int $success_count 支付成功数
 * @property int $failure_count 支付失败数
 * @property int $pending_count 支付中数
 */
final class ModelBankDisbursementUpload extends BasicModel
{
    /**
     * The table associated with the model.
     * @var string
     */
    protected $table = 'bank_disbursement_upload';

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
        'attachment_id',
        'file_name',
        'path',
        'hash',
        'file_size',
        'record_count',
        'created_by',
        'created_at',
        'suffix',
        'channel_id',
        'upload_bill_template_id',
        'parsing_status',
        'success_count',
        'failure_count',
        'pending_count',

    ];

    protected $casts = [
        'id'                      => 'integer',
        'attachment_id'           => 'integer',
        'record_count'            => 'integer',
        'created_by'              => 'integer',
        'channel_id'              => 'integer',
        'parsing_status'          => 'integer',
        'success_count'           => 'integer',
        'failure_count'           => 'integer',
        'pending_count'           => 'integer',
        'upload_bill_template_id' => DisbursementOrderBillTemplate::class,
    ];

    public function channel()
    {
        return $this->belongsTo(ModelChannel::class, 'channel_id', 'id');
    }

    public static function boot()
    {
        parent::boot();

        self::created(static function (ModelBankDisbursementUpload $model) {
            Event::dispatch('app.transaction.bank_disbursement_upload', $model);
        });
    }

}
