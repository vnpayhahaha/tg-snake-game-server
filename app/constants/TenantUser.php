<?php

namespace app\constants;

use app\constants\lib\ConstantsOptionTrait;

class TenantUser
{
    use ConstantsOptionTrait;

    // 启用状态(1正常 0停用)
    public const STATUS_NORMAL  = 0;
    public const STATUS_DISABLE = 1;
    public static array $status_list = [
        self::STATUS_NORMAL  => 'tenant_user.enums.status.1',
        self::STATUS_DISABLE => 'tenant_user.enums.status.2',
    ];



    // google验证(1正常 0停用)
    public const GOOGLE_STATUS_NORMAL  = 1;
    public const GOOGLE_STATUS_DISABLE = 0;
    public static array $google_status_list = [
        self::GOOGLE_STATUS_NORMAL  => 'tenant_user.enums.is_enabled_google.1',
        self::GOOGLE_STATUS_DISABLE => 'tenant_user.enums.is_enabled_google.2',
    ];

    // 是否已绑定Google验证(1yes 0no)
    public const GOOGLE_BIND_YES = 1;
    public const GOOGLE_BIND_NO  = 0;
    public static array $google_bind_list = [
        self::GOOGLE_BIND_YES => 'tenant_user.enums.is_bind_google.1',
        self::GOOGLE_BIND_NO  => 'tenant_user.enums.is_bind_google.2',
    ];
}
