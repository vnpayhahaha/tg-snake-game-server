<?php

namespace app\model;

use Carbon\Carbon;
use Webman\Event\Event;

/**
 * @property int $id 主键 自增id
 * @property string $hash 哈希值
 * @property string $content 内容
 * @property string $source 来源
 * @property int $status 状态：0未解析 1解析成功 2解析失败
 * @property int $repeat_count 计数
 * @property Carbon $created_at 创建时间
 * @property Carbon $updated_at 更新时间
 * @property int $channel_id 渠道id
 * @property int $bank_account_id 银行账户id
 */
final class ModelTransactionRawData extends BasicModel
{
    /**
     * The table associated with the model.
     * @var string
     */
    protected $table = 'transaction_raw_data';

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
        'hash',
        'content',
        'source',
        'status',
        'repeat_count',
        'created_at',
        'updated_at',
        'bank_account_id',
        'channel_id'
    ];

    protected $casts = [
        'id'              => 'integer',
        'status'          => 'integer',
        'repeat_count'    => 'integer',
        'channel_id'      => 'integer',
        'bank_account_id' => 'integer',
        'created_at'      => 'datetime',
        'updated_at'      => 'datetime',
    ];

    public static function boot()
    {
        parent::boot();
        self::creating(static function (ModelTransactionRawData $model) {
            $model->hash = md5($model->content);
        });

        self::created(static function (ModelTransactionRawData $model) {
            Event::dispatch('app.transaction.raw_data_analysis', $model);
        });
    }

    public function channel(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ModelChannel::class, 'channel_id', 'id');
    }

    // bank_account_id
    public function bank_account(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ModelBankAccount::class, 'bank_account_id', 'id');
    }

    // has many transaction_parsing_log
    public function transaction_parsing_log(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ModelTransactionParsingLog::class, 'raw_data_id', 'id');
    }
}
