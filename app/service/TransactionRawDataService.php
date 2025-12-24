<?php

namespace app\service;

use app\repository\TransactionRawDataRepository;
use DI\Attribute\Inject;

final class TransactionRawDataService extends IService
{
    #[Inject]
    public TransactionRawDataRepository $repository;
}
