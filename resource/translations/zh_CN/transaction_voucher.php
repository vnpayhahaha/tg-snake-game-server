<?php

return [
    'enums' => [
        'collection_status'        => [
            'waiting'    => '等待结算',
            'processing' => '处理中',
            'cancel'     => '撤销',
            'success'    => '成功',
            'fail'       => '失败',
        ],
        'collection_source'        => [
            'undefined'    => '未定义',
            'manual'       => '人工创建',
            'internal'     => '平台内部接口',
            'open_api'     => '平台开放下游接口',
            'upstream'     => '上游回调接口',
            'bank_receipt' => '银行回单',
        ],
        'transaction_voucher_type' => [
            'order_id'          => '订单ID',
            'platform_order_no' => '平台订单号',
            'utr'               => 'UTR',
            'amount'            => '金额',
            'upstream_order_no' => '上游订单号',
        ],
        'transaction_type'         => [
            'collection' => '代收',
            'payment'    => '代付',
        ],
    ]
];
