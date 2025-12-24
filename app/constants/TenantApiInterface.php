<?php

namespace app\constants;

use app\constants\lib\ConstantsOptionTrait;

class TenantApiInterface
{
    use ConstantsOptionTrait;

    // 请求方式:GET,POST,PUT,DELETE
    public const HTTP_METHOD_GET    = 'GET';
    public const HTTP_METHOD_POST   = 'POST';
    public const HTTP_METHOD_PUT    = 'PUT';
    public const HTTP_METHOD_DELETE = 'DELETE';
    public static array $http_method_list = [
        self::HTTP_METHOD_GET    => 'GET',
        self::HTTP_METHOD_POST   => 'POST',
        self::HTTP_METHOD_PUT    => 'PUT',
        self::HTTP_METHOD_DELETE => 'DELETE',
    ];

    // 认证模式 (0不需要认证 1MD5签名 2JWT 3AES)
    public const AUTH_MODE_NONE = 0;
    public const AUTH_MODE_MD5  = 1;
    public const AUTH_MODE_JWT  = 2;
    public const AUTH_MODE_AES  = 3;
    public static array $auth_mode_list = [
        self::AUTH_MODE_NONE => 'NONE',
        self::AUTH_MODE_MD5  => 'MD5',
        self::AUTH_MODE_JWT  => 'JWT',
        self::AUTH_MODE_AES  => 'AES',
    ];
}
