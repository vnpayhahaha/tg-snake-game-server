<?php

namespace app\service;

use app\constants\TransactionVoucher;
use app\repository\TransactionVoucherRepository;
use DI\Attribute\Inject;
use Illuminate\Database\Eloquent\Builder;

final class TransactionVoucherService extends IService
{
    #[Inject]
    public TransactionVoucherRepository $repository;

    public function getWriteOffOptions(array $params): array
    {
        $query = $this->repository->getQuery();
        $select = $this->repository->handleSearch($query, $params)
            ->where(function (Builder $query) {
                $query->where('collection_status', TransactionVoucher::COLLECTION_STATUS_FAIL)
                    ->orWhere('collection_status', TransactionVoucher::COLLECTION_STATUS_WAITING);
            })
            ->select(['id', 'transaction_voucher_type', 'transaction_voucher', 'collection_amount'])
            ->get();
        if (!$select) {
            return [];
        }
        $selectData = $select->toArray();
        // 根据 transaction_voucher_type 分组
        $groupType = [];
        foreach ($selectData as $item) {
            $item['collection_amount'] = number_format($item['collection_amount'], 2);
            $groupType[$item['transaction_voucher_type']][] = $item;
        }
        $result = [];
        foreach ($groupType as $key => $item) {
            $result[] = [
                'transaction_voucher_type' => (int)$key,
                'children'                 => $item,
            ];
        }
        return $result;
    }
}
