<?php

namespace app\service;

use app\repository\ChannelRepository;
use DI\Attribute\Inject;

final class ChannelService extends IService
{
    #[Inject]
    public ChannelRepository $repository;
}
