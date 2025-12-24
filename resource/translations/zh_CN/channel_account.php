<?php

return [
    'id'                   => 'ID',
    'channel_id'           => '渠道ID',
    'merchant_id'          => '商户ID',
    'api_config'           => 'API配置参数',
    'document_info'        => '文档信息',
    'api_version'          => 'API版本',
    'callback_url'         => '回调URL',
    'ip_whitelist'         => 'IP白名单',
    'balance'              => '余额',
    'currency'             => '货币代码',
    'used_quota'           => '已用额度',
    'limit_quota'          => '额度限制',
    'today_receipt_count'  => '今日收款笔数',
    'today_payment_count'  => '今日付款笔数',
    'today_receipt_amount' => '今日收款金额',
    'today_payment_amount' => '今日付款金额',
    'stat_date'            => '统计日期',
    'status'               => '状态',
    'created_at'           => '创建时间',
    'updated_at'           => '更新时间',
    'support_collection'   => '支持收款',
    'support_disbursement' => '支持付款',
    'enums'                => [
        'status'               => [
            1 => '启用',
            2 => '禁用',
        ],
        'support_collection'   => [
            1 => '支持收款',
            2 => '不支持收款',
        ],
        'support_disbursement' => [
            1 => '支持付款',
            2 => '不支持付款',
        ]

    ]
];
