<?php

use app\model\ModelUser;
use http\backend\Event\UserEvent;

return [
    'enable'  => true,
    'manager' => [
        //是否开启黑名单，单点登录和多点登录的注销、刷新使原token失效，必须要开启黑名单
        'blacklist_enabled'      => true,
        //黑名单缓存的前缀
        'blacklist_prefix'       => 'webman',
        //黑名单的宽限时间 单位为：秒，注意：如果使用单点登录，该宽限时间无效
        'blacklist_grace_period' => 0,
    ],
    'stores'  => [
        // 单应用
        'default' => [],
        // 多应用
        'backend'   => [
            'login_type'    => env('JWT_LOGIN_TYPE', 'mpo'),  //  登录方式，sso为单点登录，mpo为多点登录
            'signer_key'    => 'c7d801beaf719d2552c0c933254695c764056df482e883fb2d71092195a2da3e',
            'public_key'    => 'file://path/public.key',
            'private_key'   => 'file://path/private.key',
            'expires_at'    => 3600,
            'refresh_ttL'   => 7200,
            'leeway'        => 0,
            'signer'        => 'HS256',
            'type'          => 'Header',
            'auto_refresh'  => true,
            'iss'           => 'webman.admin',
            'event_handler' => UserEvent::class,
            'user_model'    => ModelUser::class
        ],
        // 多应用
        'tenant'   => [
            'login_type'    => env('JWT_LOGIN_TYPE', 'mpo'), //  登录方式，sso为单点登录，mpo为多点登录
            'signer_key'    => 'c7d801beaf719d2552c0c933254695c764056df482e883fb2d71092195a2da3d',
            'public_key'    => 'file://path/public.key',
            'private_key'   => 'file://path/private.key',
            'expires_at'    => 3600,
            'refresh_ttL'   => 7200,
            'leeway'        => 0,
            'signer'        => 'HS256',
            'type'          => 'Header',
            'auto_refresh'  => true,
            'iss'           => 'webman.tenant',
            'event_handler' => \http\tenant\Event\TenantUserEvent::class,
            'user_model'    => \app\model\ModelTenantUser::class
        ],
    ]
];
