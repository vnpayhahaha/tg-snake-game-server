<?php

namespace app\model;

use Carbon\Carbon;

/**
 * @property int $id 主键 自增ID
 * @property int $raw_data_id 原始数据ID
 * @property int $rule_id 规则ID
 * @property string $rule_text 规则内容
 * @property string $variable_name 记录匹配变量名称
 * @property int $status 状态：1解析成功 2失败或部分失败
 * @property int $voucher_id 凭证ID
 * @property Carbon $created_at 创建时间
 * @property string $fail_msg 失败原因说明
 * @property string $fail_msg_en 失败原因说明
 */
final class ModelTransactionParsingLog extends BasicModel
{
    /**
     * The table associated with the model.
     * @var string
     */
    protected $table = 'transaction_parsing_log';

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
        'raw_data_id',
        'rule_id',
        'rule_text',
        'variable_name',
        'status',
        'voucher_id',
        'created_at',
        'fail_msg',
        'fail_msg_en'
    ];

    protected $casts = [
        'id'            => 'integer',
        'raw_data_id'   => 'integer',
        'rule_id'       => 'integer',
        'rule_text'     => 'string',
        'variable_name' => 'array',
        'status'        => 'integer',
        'voucher_id'    => 'integer',
        'created_at'    => 'datetime',
        'fail_msg'      => 'string',
        'fail_msg_en'   => 'string',
    ];
}
