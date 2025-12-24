<?php

return [
    'enums' => [
        'collection_status'        => [
            'waiting'    => 'waiting',
            'processing' => 'processing',
            'cancel'     => 'cancel',
            'success'    => 'success',
            'fail'       => 'fail',
        ],
        'collection_source'        => [
            'undefined'    => 'unkown',
            'manual'       => 'manual',
            'internal'     => 'internal',
            'open_api'     => 'open_api',
            'upstream'     => 'external',
            'bank_receipt' => 'bank_receipt',
        ],
        'transaction_voucher_type' => [
            'order_id'          => 'Order Id',
            'platform_order_no' => 'Platform Order No',
            'utr'               => 'UTR',
            'amount'            => 'Amount',
            'upstream_order_no' => 'Upstream Order No',
        ],
        'transaction_type'         => [
            'collection' => '代收',
            'payment'    => '代付',
        ],
    ]
];
