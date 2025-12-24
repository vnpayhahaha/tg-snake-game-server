<?php

namespace app\service;

use app\repository\TenantAccountRepository;
use app\repository\TransactionRecordRepository;
use DI\Attribute\Inject;

final class TenantAccountService extends BaseService
{
    #[Inject]
    public TenantAccountRepository $repository;

    // TransactionRecordRepository
    #[Inject]
    public TransactionRecordRepository $transactionRecordRepository;

    public function changeBalanceAvailable(int $id, float $changeAmount): bool
    {
        // 查询账户信息
        $account = $this->repository->findById($id);
        if (!$account) {
            var_dump('账户不存在：', $id);
            return false;
        }
        return $this->transactionRecordRepository->adjustFunds($this->getCurrentUserId(), $this->getCurrentUserName(), $account, $changeAmount);
    }
    public function changeBalanceFrozen(int $id, float $changeAmount): bool
    {
        // 查询账户信息
        $account = $this->repository->findById($id);
        if (!$account) {
            var_dump('账户不存在：', $id);
            return false;
        }
        return $this->transactionRecordRepository->freezeFunds($this->getCurrentUserId(), $this->getCurrentUserName(), $account, $changeAmount);
    }
}
