<?php

namespace app\service;

use app\repository\TransactionParsingLogRepository;
use DI\Attribute\Inject;

final class TransactionParsingLogService extends IService
{
    #[Inject]
    public TransactionParsingLogRepository $repository;

}
