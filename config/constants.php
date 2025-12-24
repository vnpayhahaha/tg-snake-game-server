<?php

return [
    'menu'                      => app\constants\Menu::class,
    // 菜单状态
    'policy'                    => app\constants\Policy::class,
    // 策略状态
    'role'                      => app\constants\Role::class,
    // 角色状态
    'user'                      => app\constants\User::class,
    // 用户状态
    'tenant'                    => app\constants\Tenant::class,
    // 租户状态
    'tenant_app'                => app\constants\TenantApp::class,
    // 租户应用状态
    'tenant_user'               => app\constants\TenantUser::class,
    // 租户用户状态
    'tenant_config'             => app\constants\TenantConfig::class,
    // 租户配置状态
    'channel'                   => app\constants\Channel::class,
    // 渠道状态
    'channel_account'           => app\constants\ChannelAccount::class,
    // 渠道账户状态
    'bank_account'              => app\constants\BankAccount::class,
    // 银行账户状态
    'tenant_account'            => app\constants\TenantAccount::class,
    // 租户账户状态
    'transaction_record'        => app\constants\TransactionRecord::class,
    // 交易记录状态
    'transaction_queue_status'  => app\constants\TransactionQueueStatus::class,
    // 交易队列状态
    'tenant_account_record'     => app\constants\TenantAccountRecord::class,
    // 租户账户记录状态
    'tenant_api_interface'      => app\constants\TenantApiInterface::class,
    // 租户接口状态
    'transaction_voucher'       => app\constants\TransactionVoucher::class,
    // 交易凭证状态
    'collection_order'          => app\constants\CollectionOrder::class,
    // 收款订单状态
    'disbursement_order'        => app\constants\DisbursementOrder::class,
    // 提现订单状态
    'transaction_parsing_rules' => app\constants\TransactionParsingRules::class,
    // 交易解析规则
    'transaction_raw_data'      => app\constants\TransactionRawData::class,
    // 交易原始数据
    'bank_disbursement_upload'  => app\constants\BankDisbursementUpload::class,
    // 银行代付上传
    'tenant_notification_queue' => app\constants\TenantNotificationQueue::class,
    // ChannelCallbackRecord
    'channel_callback_record'   => app\constants\ChannelCallbackRecord::class,
];
