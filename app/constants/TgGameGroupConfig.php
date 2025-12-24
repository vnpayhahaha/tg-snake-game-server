<?php

namespace app\constants;

use app\constants\lib\ConstantsOptionTrait;

class TgGameGroupConfig
{
    use ConstantsOptionTrait;

    // 群组状态: 1-启用 0-禁用
    public const STATUS_DISABLED = 0;
    public const STATUS_ENABLED = 1;
    public static array $status_list = [
        self::STATUS_DISABLED => 'tg_game_group_config.enums.status.0',
        self::STATUS_ENABLED  => 'tg_game_group_config.enums.status.1',
    ];

    // 钱包变更状态: 1-正常 2-变更中
    public const WALLET_CHANGE_STATUS_NORMAL = 1;
    public const WALLET_CHANGE_STATUS_CHANGING = 2;
    public static array $wallet_change_status_list = [
        self::WALLET_CHANGE_STATUS_NORMAL   => 'tg_game_group_config.enums.wallet_change_status.1',
        self::WALLET_CHANGE_STATUS_CHANGING => 'tg_game_group_config.enums.wallet_change_status.2',
    ];

    // 配置变更来源: 1-后台编辑 2-TG群指令
    public const CHANGE_SOURCE_BACKEND = 1;
    public const CHANGE_SOURCE_TELEGRAM = 2;
    public static array $change_source_list = [
        self::CHANGE_SOURCE_BACKEND  => 'tg_game_group_config.enums.change_source.1',
        self::CHANGE_SOURCE_TELEGRAM => 'tg_game_group_config.enums.change_source.2',
    ];

    // 默认冷却时间（分钟）
    public const DEFAULT_WALLET_COOLDOWN_MINUTES = 10;

    // 默认平台费率（10%）
    public const DEFAULT_PLATFORM_FEE_RATE = 0.10;

    // 默认最小投注金额（TRX）
    public const DEFAULT_MIN_BET_AMOUNT = 100;
}
