<?php

return [
    'user_id'           => 'ID',
    'tenant_id'         => '租户编号',
    'username'          => '用户名',
    'password'          => '密码',
    'phone'             => '手机号码',
    'avatar'            => '头像',
    'last_login_ip'     => '最后登录IP',
    'last_login_time'   => '最后登录时间',
    'status'            => '状态',
    'is_enabled_google' => '是否启用谷歌验证',
    'google_secret'     => '谷歌验证密钥',
    'is_bind_google'    => '是否绑定谷歌验证',
    'created_by'        => '创建者',
    'created_at'        => '创建时间',
    'updated_by'        => '更新者',
    'updated_at'        => '更新时间',
    'deleted_by'        => '删除者',
    'deleted_at'        => '删除时间',
    'remark'            => '备注',
    'ip_whitelist'      => 'IP白名单',
    'enums'             => [
        'status'            => [
            1 => '正常',
            2 => '停用',
        ],
        'is_enabled_google' => [
            1 => '是',
            2 => '否',
        ],
        'is_bind_google'    => [
            1 => '是',
            2 => '否',
        ]
    ]
];
