<?php

namespace app\service;

use app\repository\TransactionQueueStatusRepository;
use DI\Attribute\Inject;

final class TransactionQueueStatusService extends IService
{
    #[Inject]
    public TransactionQueueStatusRepository $repository;

}
