<?php

return [
    'id'                => 'ID',
    'tenant_id'         => '租户编号',
    'contact_user_name' => '联系人',
    'contact_phone'     => '联系电话',
    'company_name'      => '企业名称',
    'license_number'    => '企业代码',
    'address'           => '地址',
    'intro'             => '企业简介',
    'domain'            => '域名',
    'account_count'     => '用户数量',
    'is_enabled'        => '启用状态',
    'created_by'        => '创建管理员',
    'created_at'        => '创建时间',
    'expired_at'        => '过期时间',
    'updated_by'        => '更新者',
    'updated_at'        => '更新时间',
    'safe_level'        => '安全等级(0-99)',
    'deleted_by'        => '删除者',
    'deleted_at'        => '删除时间',
    'remark'            => '备注',
    'enums'             => [
        'is_enabled'            => [
            1 => '激活',
            2 => '停用',
        ],
        'settlement'            => [
            1 => '实际金额',
            2 => '订单金额',
        ],
        'bank_card'             => [
            1 => '随机',
            2 => '依次',
            3 => '轮询',
        ],
        'collection_use_method' => [
            'bank_account' => '银行户',
            'upstream'     => '上游',
        ],
    ],
];
