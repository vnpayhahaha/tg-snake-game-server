<?php

return [
    'id'                   => 'ID',
    'channel_id'           => 'Channel ID',
    'merchant_id'          => 'Merchant ID',
    'api_config'           => 'API configuration',
    'document_info'        => 'document information',
    'api_version'          => 'API version',
    'callback_url'         => 'Callback URL',
    'ip_whitelist'         => 'IP whitelist',
    'balance'              => 'balance',
    'currency'             => 'Currency',
    'used_quota'           => 'Used credit limit',
    'limit_quota'          => 'Line of limit',
    'today_receipt_count'  => 'Number of payments received today',
    'today_payment_count'  => 'Number of payments made today',
    'today_receipt_amount' => "Today's receipt amount",
    'today_payment_amount' => "Today's payment amount",
    'stat_date'            => 'stat_date',
    'status'               => 'state',
    'created_at'           => 'Creation time',
    'updated_at'           => 'update time',
    'support_collection'   => 'Support payment collection',
    'support_disbursement' => 'Support payment',
    'enums'                => [
        'status'               => [
            1 => 'Enable',
            2 => 'Disable',
        ],
        'support_collection'   => [
            1 => 'Support',
            2 => 'Not Supported',
        ],
        'support_disbursement' => [
            1 => 'Support',
            2 => 'Not Supported',
        ]

    ]
];
