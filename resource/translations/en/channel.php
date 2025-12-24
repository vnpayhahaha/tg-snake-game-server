<?php
return [
    'id'                   => 'ID',
    'channel_code'         => 'Channel Number',
    'channel_name'         => 'Channel Name',
    'channel_icon'         => 'Channel icon',
    'channel_type'         => 'Channel type',
    'country_code'         => 'Country code',
    'currency'             => 'Currency',
    'api_base_url'         => 'API Basic URL',
    'doc_url'              => 'Document URL',
    'support_collection'   => 'Support payment collection',
    'support_disbursement' => 'Support payment',
    'config'               => 'configuration parameter',
    'status'               => 'state',
    'created_at'           => 'Creation time',
    'updated_at'           => 'update time',
    'deleted_at'           => 'Delete Time',
    'enums'                => [
        'status'               => [
            1 => 'Enable',
            2 => 'Disable',
        ],
        'channel_type'         => [
            1 => 'bank',
            2 => 'third party',
        ],
        'support_collection'   => [
            1 => 'Support payment collection',
            2 => 'Not supported for receiving payments',
        ],
        'support_disbursement' => [
            1 => 'Support payment',
            2 => 'Payment not supported',
        ],
    ],
];
