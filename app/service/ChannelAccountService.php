<?php

namespace app\service;

use app\repository\ChannelAccountRepository;
use DI\Attribute\Inject;

final class ChannelAccountService extends IService
{
    #[Inject]
    public ChannelAccountRepository $repository;
}
