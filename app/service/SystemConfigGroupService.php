<?php

namespace app\service;

use app\repository\SystemConfigGroupRepository;
use DI\Attribute\Inject;

/**
 * @extends IService<SystemConfigGroupRepository>
 */
class SystemConfigGroupService  extends IService
{
    #[Inject]
    protected SystemConfigGroupRepository $repository;
}
