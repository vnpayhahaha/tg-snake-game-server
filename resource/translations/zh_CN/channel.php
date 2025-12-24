<?php
return [
    'id'                   => 'ID',
    'channel_code'         => '渠道编号',
    'channel_name'         => '渠道名称',
    'channel_icon'         => '渠道图标',
    'channel_type'         => '渠道类型',
    'country_code'         => '国家代码',
    'currency'             => '货币代码',
    'api_base_url'         => 'API基础URL',
    'doc_url'              => '文档URL',
    'support_collection'   => '支持收款',
    'support_disbursement' => '支持付款',
    'config'               => '配置参数',
    'status'               => '状态',
    'created_at'           => '创建时间',
    'updated_at'           => '更新时间',
    'deleted_at'           => '删除时间',
    'enums'                => [
        'status'               => [
            1 => '启用',
            2 => '禁用',
        ],
        'channel_type'         => [
            1 => '银行',
            2 => '第三方',
        ],
        'support_collection'   => [
            1 => '支持收款',
            2 => '不支持收款',
        ],
        'support_disbursement' => [
            1 => '支持付款',
            2 => '不支持付款',
        ],
    ],
];
