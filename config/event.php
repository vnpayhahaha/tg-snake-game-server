<?php

return [
    'operation.log'                            => [
        [\app\event\OperationEvent::class, 'process'],
    ],
    'tenant.app.log'                           => [
        [\app\event\TenantAppLogEvent::class, 'process'],
    ],
    'collection-order-status-records'          => [
        [\app\event\CollectionOrderStatusRecordsEvent::class, 'process'],
    ],
    'disbursement-order-status-records'        => [
        [\app\event\DisbursementOrderStatusRecordsEvent::class, 'process'],
    ],
    'app.tenant.created'                       => [
        [\app\event\TenantEvent::class, 'Created'],
    ],
    'app.tenant.auto_assign'                   => [
        [\app\event\TenantEvent::class, 'AutoAssign'],
    ],
    'app.transaction.created'                  => [
        [\app\event\TransactionRecordEvent::class, 'Created'],
    ],
    'app.transaction.failed'                   => [
        [\app\event\TransactionRecordEvent::class, 'Failed'],
    ],
    'app.transaction.success'                  => [
        [\app\event\TransactionRecordEvent::class, 'Success'],
    ],
    'app.transaction.raw_data_analysis'        => [
        [\app\event\TransactionRawDataEvent::class, 'Created'],
    ],
    'app.transaction.bank_disbursement_upload' => [
        [\app\event\BankDisbursementUploadEvent::class, 'Created'],
    ],
    'backend.user.login'                       => [
        [\http\backend\Service\PassportService::class, 'loginLog'],
    ],
    'tenant.user.login'                        => [
        [\http\tenant\Service\PassportService::class, 'loginLog'],
    ],
    // 在服务停止时清理监听器跟踪
    'stop'                                     => static function () {
        \app\process\CacheableProcessor::clearListeners();
    }
];
