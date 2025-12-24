<?php

namespace app\service;

use app\repository\ChannelRequestRecordRepository;
use DI\Attribute\Inject;

final class ChannelRequestRecordService extends IService
{
    #[Inject]
    public ChannelRequestRecordRepository $repository;
}
