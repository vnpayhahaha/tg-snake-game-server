<?php

namespace app\model;

use Carbon\Carbon;

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
*/
final class ModelBankDisbursementDownload extends BasicModel
{
    /**
     * The table associated with the model.
     * @var string
     */
    protected $table = 'bank_disbursement_download';

    /**
     * The primary key associated with the table.
     * @var string
     */
    protected $primaryKey = 'id';

    public $timestamps = false;
    
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
        'suffix'
    ];
}
