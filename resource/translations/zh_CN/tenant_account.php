<?php

return [
    'id'                => 'id',
    'tenant_id'         => '租户编号',
    'balance_available' => '可用余额',
    'balance_frozen'    => '冻结余额',
    'account_type'      => '账户类型',
    'version'           => '乐观锁版本',
    'created_at'        => '创建时间',
    'updated_at'        => '更新时间',
    'enums'             => [
        'account_type' => [
            1 => '收款账户',
            2 => '付款账户',
        ],
    ],

];
