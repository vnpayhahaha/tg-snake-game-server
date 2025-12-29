<?php

namespace app\model;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
* @property int $id 主键 主键
* @property int $config_id 配置表ID
* @property int $tg_chat_id Telegram群组ID
* @property float $prize_pool_amount 当前奖池金额
* @property string $current_snake_nodes 当前蛇身节点ID（逗号分割）
* @property string $last_snake_nodes 上次蛇身节点ID（逗号分割）
* @property string $last_prize_nodes 上次中奖区间节点ID（逗号分割）
* @property float $last_prize_amount 上次中奖金额
* @property string $last_prize_address 上次中奖地址（多个用逗号分割）
* @property string $last_prize_serial_no 上次开奖流水号
* @property Carbon $last_prize_at 上次中奖时间
* @property int $version 乐观锁版本号
* @property Carbon $created_at 创建时间
* @property Carbon $updated_at 更新时间
*/
final class ModelTgGameGroup extends BasicModel
{
    /**
     * The table associated with the model.
     * @var string
     */
    protected $table = 'tg_game_group';

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
        'config_id',
        'tg_chat_id',
        'prize_pool_amount',
        'current_snake_nodes',
        'last_snake_nodes',
        'last_prize_nodes',
        'last_prize_amount',
        'last_prize_address',
        'last_prize_serial_no',
        'last_prize_at',
        'version',
        'created_at',
        'updated_at'
    ];

    /**
     * 关联群组配置（一对一）
     * @return BelongsTo
     */
    public function config(): BelongsTo
    {
        return $this->belongsTo(ModelTgGameGroupConfig::class, 'config_id', 'id');
    }
}
