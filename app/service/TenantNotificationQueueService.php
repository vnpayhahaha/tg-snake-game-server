<?php

namespace app\service;

use app\repository\TenantNotificationQueueRepository;
use DI\Attribute\Inject;

class TenantNotificationQueueService extends IService
{
    #[Inject]
    public TenantNotificationQueueRepository $repository;
}
