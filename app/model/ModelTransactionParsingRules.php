<?php

namespace app\model;

use app\model\lib\CustomSoftDeletes;
use Carbon\Carbon;

/**
 * @property int $id 主键 自增id
 * @property int $channel_id 渠道ID
 * @property string $regex 正则表达式
 * @property string $variable_name 提取变量名
 * @property string $example_data 示例数据
 * @property int $status 状态：1启用 0禁用
 * @property Carbon $created_at 创建时间
 * @property Carbon $updated_at 更新时间
 * @property Carbon $deleted_at 删除时间
 */
final class ModelTransactionParsingRules extends BasicModel
{
    use CustomSoftDeletes;
    /**
     * The table associated with the model.
     * @var string
     */
    protected $table = 'transaction_parsing_rules';

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
        'regex',
        'variable_name',
        'example_data',
        'status',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    protected $casts = [
        'id'            => 'integer',
        'status'        => 'boolean',
        'channel_id'    => 'integer',
        'variable_name' => 'array',
        'created_at'    => 'datetime',
        'updated_at'    => 'datetime',
        'deleted_at'    => 'datetime',
    ];

    public function channel()
    {
        return $this->belongsTo(ModelChannel::class, 'channel_id','id' );
    }
}
