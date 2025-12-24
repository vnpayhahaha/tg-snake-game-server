<?php

namespace app\service;

use app\repository\TenantAccountRecordRepository;
use DI\Attribute\Inject;

final class TenantAccountRecordService extends BaseService
{
    #[Inject]
    public TenantAccountRecordRepository $repository;

}
