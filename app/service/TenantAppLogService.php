<?php

namespace app\service;

use app\repository\TenantAppLogRepository;
use DI\Attribute\Inject;

class TenantAppLogService extends IService
{
    #[Inject]
    protected TenantAppLogRepository $repository;
}
