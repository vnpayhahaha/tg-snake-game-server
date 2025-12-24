<?php
return [
    'id'                      => 'ID',
    'channel_id'              => 'Bank ID',
    'branch_name'             => 'bank branch',
    'account_holder'          => 'account holder',
    'account_number'          => 'account number',
    'bank_code'               => 'Bank IFSC code',
    'created_at'              => 'Creation time',
    'updated_at'              => 'update time',
    'deleted_at'              => 'Delete Time',
    'balance'                 => 'balance',
    'float_amount_enabled'    => 'Do you want to enable floating amounts',
    'daily_max_receipt'       => 'daily max receipt',
    'daily_max_payment'       => 'daily max payment',
    'daily_max_receipt_count' => 'daily max receipt count',
    'daily_max_payment_count' => 'daily max payment count',
    'max_receipt_per_txn'     => 'max receipt per txn',
    'max_payment_per_txn'     => 'max payment per txn',
    'min_receipt_per_txn'     => 'min receipt per txn',
    'min_payment_per_txn'     => 'min payment per txn',
    'security_level'          => 'security level',
    'last_used_time'          => 'last used time',
    'upi_id'                  => 'UPI ID',
    'used_quota'              => 'used quota',
    'limit_quota'             => 'limit quota',
    'today_receipt_count'     => 'today receipt count',
    'today_payment_count'     => 'today payment count',
    'today_receipt_amount'    => 'today receipt amount',
    'today_payment_amount'    => 'today payment amount',
    'stat_date'               => 'stat date',
    'status'                  => 'state',
    'support_collection'      => 'Support payment collection',
    'support_disbursement'    => 'Support payment',
    'enums'                   => [
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
