<?php

namespace app\service;

use app\repository\ChannelRepository;
use DI\Attribute\Inject;

/**
 * @extends IService<ChannelRepository>
 */
final class TenantConfigService extends IService
{
    #[Inject]
    public ChannelRepository $repository;

}
