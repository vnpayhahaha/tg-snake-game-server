<?php

namespace app\repository;

use app\model\ModelTenantAppLog;
use DI\Attribute\Inject;

/**
 * Class TenantAppLogRepository.
 * @extends IRepository<ModelTenantAppLog>
 */
class TenantAppLogRepository extends IRepository
{
    #[Inject]
    protected ModelTenantAppLog $model;
}
