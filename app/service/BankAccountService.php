<?php

namespace app\service;

use app\repository\BankAccountRepository;
use DI\Attribute\Inject;

final class BankAccountService extends IService
{
    #[Inject]
    public BankAccountRepository $repository;

    public function getDownBillTemplateIds(int $bankAccountId): array
    {
        return $this->repository->getQuery()->where('id', $bankAccountId)->value('down_bill_template_id') ?? [];
    }
}
