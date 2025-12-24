<?php

namespace app\service;

use app\repository\UserOperationLogRepository;
use DI\Attribute\Inject;

final class UserOperationLogService extends IService
{
    #[Inject]
    protected UserOperationLogRepository $repository;

}
