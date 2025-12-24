<?php

namespace app\service;

use app\repository\TransactionParsingRulesRepository;
use DI\Attribute\Inject;


final class TransactionParsingRulesService extends IService
{
    #[Inject]
    public TransactionParsingRulesRepository $repository;
}
