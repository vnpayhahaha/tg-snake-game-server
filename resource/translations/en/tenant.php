<?php

return [
    'id'                => 'ID',
    'tenant_id'         => 'Tenant Number',
    'contact_user_name' => 'Contact Person',
    'contact_phone'     => 'Contact Phone',
    'company_name'      => 'Company Name',
    'license_number'    => 'Enterprise Code',
    'address'           => 'Address',
    'intro'             => 'Company Introduction',
    'domain'            => 'Domain',
    'account_count'     => 'User Count',
    'is_enabled'        => 'Status',
    'created_by'        => 'Creator',
    'created_at'        => 'Creation Time',
    'expired_at'        => 'Expiration Time',
    'updated_by'        => 'Updater',
    'updated_at'        => 'Update Time',
    'safe_level'        => 'Safe Level',
    'deleted_by'        => 'Deleter',
    'deleted_at'        => 'Deletion Time',
    'remark'            => 'Remark',
    'enums'             => [
        'is_enabled'            => [
            1 => 'Active',
            2 => 'Disabled'
        ],
        'settlement'            => [
            1 => 'Actual Amount',
            2 => 'Order Amount'
        ],
        'bank_card'             => [
            1 => 'Random',
            2 => 'Sequential',
            3 => 'Polling'
        ],
        'collection_use_method' => [
            'bank_account' => 'Bank Account',
            'upstream'  => 'Upstream',
        ],
    ]
];
