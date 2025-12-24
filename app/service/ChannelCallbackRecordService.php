<?php

namespace app\service;

use app\repository\ChannelCallbackRecordRepository;
use DI\Attribute\Inject;

final class ChannelCallbackRecordService extends IService
{
    #[Inject]
    public ChannelCallbackRecordRepository $repository;
}
