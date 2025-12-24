<?php

namespace app\service;

use app\repository\TransactionRecordRepository;
use DI\Attribute\Inject;

final class TransactionRecordService extends IService
{
    #[Inject]
    public TransactionRecordRepository $repository;
}
