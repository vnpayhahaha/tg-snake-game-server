<?php

namespace app\model;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
* @property int $id 主键 主键
* @property string $tenant_id 租户ID
* @property int $tg_chat_id Telegram群组ID
* @property string $tg_chat_title 群组名称
* @property string $wallet_address TRON钱包地址
* @property int $wallet_change_count 钱包变更次数（用于区分不同钱包周期）
* @property string $pending_wallet_address 待更新的钱包地址
* @property int $wallet_change_status 钱包变更状态:1=正常,2=变更中
* @property Carbon $wallet_change_start_at 钱包变更开始时间
* @property Carbon $wallet_change_end_at 钱包变更生效时间
* @property string $hot_wallet_address 热钱包地址（用于转账）
* @property string $hot_wallet_private_key 热钱包私钥（加密存储）
* @property float $bet_amount 投注金额(TRX)
* @property float $platform_fee_rate 平台手续费比例(默认10%)
* @property string $telegram_admin_whitelist 管理员白名单（逗号分隔的TG用户ID）
* @property int $status 状态 1-正常 0-停用
* @property Carbon $created_at 创建时间
* @property Carbon $updated_at 更新时间
*/
final class ModelTgGameGroupConfig extends BasicModel
{
    /**
     * The table associated with the model.
     * @var string
     */
    protected $table = 'tg_game_group_config';

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
        'tenant_id',
        'tg_chat_id',
        'tg_chat_title',
        'wallet_address',
        'wallet_change_count',
        'pending_wallet_address',
        'wallet_change_status',
        'wallet_change_start_at',
        'wallet_change_end_at',
        'hot_wallet_address',
        'hot_wallet_private_key',
        'bet_amount',
        'platform_fee_rate',
        'telegram_admin_whitelist',
        'status',
        'created_at',
        'updated_at'
    ];

    /**
     * 获取管理员白名单数组
     * @return array
     */
    public function getAdminWhitelistArray(): array
    {
        if (empty($this->telegram_admin_whitelist)) {
            return [];
        }

        return array_map('intval', array_filter(explode(',', $this->telegram_admin_whitelist)));
    }

    /**
     * 设置管理员白名单数组
     * @param array $userIds
     * @return void
     */
    public function setAdminWhitelistArray(array $userIds): void
    {
        $this->telegram_admin_whitelist = implode(',', array_unique(array_filter($userIds)));
    }

    /**
     * 添加管理员到白名单
     * @param int $userId
     * @return bool
     */
    public function addAdminToWhitelist(int $userId): bool
    {
        $whitelist = $this->getAdminWhitelistArray();

        if (in_array($userId, $whitelist, true)) {
            return false; // 已存在
        }

        $whitelist[] = $userId;
        $this->setAdminWhitelistArray($whitelist);

        return true;
    }

    /**
     * 从白名单移除管理员
     * @param int $userId
     * @return bool
     */
    public function removeAdminFromWhitelist(int $userId): bool
    {
        $whitelist = $this->getAdminWhitelistArray();

        $key = array_search($userId, $whitelist, true);
        if ($key === false) {
            return false; // 不存在
        }

        unset($whitelist[$key]);
        $this->setAdminWhitelistArray(array_values($whitelist));

        return true;
    }

    /**
     * 检查用户是否在白名单中
     * @param int $userId
     * @return bool
     */
    public function isInAdminWhitelist(int $userId): bool
    {
        return in_array($userId, $this->getAdminWhitelistArray(), true);
    }

    /**
     * 关联游戏群组（一对一）
     * @return HasOne
     */
    public function group(): HasOne
    {
        return $this->hasOne(ModelTgGameGroup::class, 'config_id', 'id');
    }
}
