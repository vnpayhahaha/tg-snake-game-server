<?php
return [
    'id'                      => 'ID',
    'channel_id'              => '银行id',
    'branch_name'             => '支行名称',
    'account_holder'          => '账户持有人',
    'account_number'          => '账号',
    'bank_code'               => '银行IFSC代码',
    'created_at'              => '创建时间',
    'updated_at'              => '更新时间',
    'deleted_at'              => '删除时间',
    'balance'                 => '余额',
    'float_amount_enabled'    => '是否启用浮动金额',
    'daily_max_receipt'       => '单日收款最大金额',
    'daily_max_payment'       => '单日付款最大金额',
    'daily_max_receipt_count' => '单日收款最大笔数',
    'daily_max_payment_count' => '单日付款最大笔数',
    'max_receipt_per_txn'     => '单笔收款最大金额',
    'max_payment_per_txn'     => '单笔付款最大金额',
    'min_receipt_per_txn'     => '单笔收款最小金额',
    'min_payment_per_txn'     => '单笔付款最小金额',
    'security_level'          => '安全等级',
    'last_used_time'          => '最后使用时间',
    'upi_id'                  => 'UPI ID',
    'used_quota'              => '已用额度',
    'limit_quota'             => '额度限制',
    'today_receipt_count'     => '今日收款笔数',
    'today_payment_count'     => '今日付款笔数',
    'today_receipt_amount'    => '今日收款金额',
    'today_payment_amount'    => '今日付款金额',
    'stat_date'               => '统计日期',
    'status'                  => '状态',
    'support_collection'      => '支持收款',
    'support_disbursement'    => '支持付款',
    'enums'                   => [
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
