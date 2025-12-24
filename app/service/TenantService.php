<?php

namespace app\service;

use app\repository\TenantRepository;
use DI\Attribute\Inject;

/**
 * @extends IService<TenantRepository>
 */
final class TenantService extends IService
{
    #[Inject]
    public TenantRepository $repository;
}
