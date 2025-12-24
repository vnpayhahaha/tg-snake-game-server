<?php

namespace app\constants;

use app\constants\lib\ConstantsOptionTrait;

class Channel
{
    use ConstantsOptionTrait;

    // $status 状态:1-启用 2-停用
    public const STATUS_ENABLE = 1;
    public const STATUS_DISABLE = 0;
    public static array $status_list = [
        self::STATUS_ENABLE  => 'channel.enums.status.1',
        self::STATUS_DISABLE => 'channel.enums.status.2',
    ];

    // $channel_type 渠道类型:1-银行 2-上游第三方支付
    public const CHANNEL_TYPE_BANK = 1;
    public const CHANNEL_TYPE_THIRD_PARTY = 2;
    public static array $channel_type_list = [
        self::CHANNEL_TYPE_BANK          => 'channel.enums.channel_type.1',
        self::CHANNEL_TYPE_THIRD_PARTY   => 'channel.enums.channel_type.2',
    ];

    // $support_collection 支持代收
    public const SUPPORT_COLLECTION_YES = 1;
    public const SUPPORT_COLLECTION_NO = 0;
    public static array $support_collection_list = [
        self::SUPPORT_COLLECTION_YES => 'channel.enums.support_collection.1',
        self::SUPPORT_COLLECTION_NO  => 'channel.enums.support_collection.2',
    ];

    // $support_disbursement 支持代付
    public const SUPPORT_DISBURSEMENT_YES = 1;
    public const SUPPORT_DISBURSEMENT_NO = 0;
    public static array $support_disbursement_list = [
        self::SUPPORT_DISBURSEMENT_YES => 'channel.enums.support_disbursement.1',
        self::SUPPORT_DISBURSEMENT_NO  => 'channel.enums.support_disbursement.2',
    ];
}
