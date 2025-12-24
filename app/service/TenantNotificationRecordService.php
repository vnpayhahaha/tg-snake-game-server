<?php

namespace app\service;

use app\repository\TenantNotificationRecordRepository;
use DI\Attribute\Inject;

class TenantNotificationRecordService extends IService
{
    #[Inject]
    public TenantNotificationRecordRepository $repository;
}
