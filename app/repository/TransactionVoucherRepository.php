<?php

namespace app\repository;

use app\constants\TransactionVoucher;
use app\model\ModelTransactionVoucher;
use DI\Attribute\Inject;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class TransactionVoucherRepository.
 * @extends IRepository<ModelTransactionVoucher>
 */
class TransactionVoucherRepository extends IRepository
{
    #[Inject]
    protected ModelTransactionVoucher $model;

    public function handleSearch(Builder $query, array $params): Builder
    {

        if (isset($params['channel_id']) && filled($params['channel_id'])) {
            $query->where('channel_id', $params['channel_id']);
        }

        if (isset($params['channel_account_id']) && filled($params['channel_account_id'])) {
            $query->where('channel_account_id', $params['channel_account_id']);
        }

        if (isset($params['bank_account_id']) && filled($params['bank_account_id'])) {
            $query->where('bank_account_id', $params['bank_account_id']);
        }

        if (isset($params['collection_card_no']) && filled($params['collection_card_no'])) {
            $query->where('collection_card_no', $params['collection_card_no']);
        }

        if (isset($params['collection_time']) && filled($params['collection_time'])) {
            $query->where('collection_time', $params['collection_time']);
        }

        if (isset($params['collection_status']) && filled($params['collection_status'])) {
            $query->where('collection_status', $params['collection_status']);
        }

        if (isset($params['collection_source']) && filled($params['collection_source'])) {
            $query->where('collection_source', $params['collection_source']);
        }

        if (isset($params['transaction_voucher_type']) && filled($params['transaction_voucher_type'])) {
            $query->where('transaction_voucher_type', $params['transaction_voucher_type']);
        }

        if (isset($params['order_no']) && filled($params['order_no'])) {
            $query->where('order_no', $params['order_no']);
        }

        if (isset($params['transaction_type']) && filled($params['transaction_type'])) {
            $query->where('transaction_type', $params['transaction_type']);
        }

        return $query;
    }

    public function page(array $params = [], ?int $page = null, ?int $pageSize = null): array
    {
        $result = $this->perQuery($this->getQuery(), $params)
            ->with('channel:id,channel_name,channel_code,channel_icon')
            ->with('channel_account:id,merchant_id')
            ->with('bank_account:id,branch_name,account_holder,account_number,upi_id,bank_code')
            ->paginate(
                perPage: $pageSize,
                pageName: static::PER_PAGE_PARAM_NAME,
                page: $page,
            );
        return $this->handlePage($result);
    }

    // 核销
    public function writeOff(int $transactionVoucherId, string $platform_order_no): bool
    {
        $find = $this->model::where('id', $transactionVoucherId)
            ->first();
        if (!$find) {
            throw new \RuntimeException('The verification certificate does not exist');
        }
        if (!in_array($find->collection_status, [
            TransactionVoucher::COLLECTION_STATUS_WAITING,
            TransactionVoucher::COLLECTION_STATUS_FAIL
        ], true)) {
            throw new \RuntimeException('The verification failed, please check the status of the verification certificate:' . TransactionVoucher::getHumanizeValue(TransactionVoucher::$collection_status_list, $find->collection_status));
        }
        return $this->model::where('id', $transactionVoucherId)
                ->where(function (Builder $query) {
                    $query->where('collection_status', TransactionVoucher::COLLECTION_STATUS_FAIL)
                        ->orWhere('collection_status', TransactionVoucher::COLLECTION_STATUS_WAITING);
                })
                ->update([
                    'collection_status' => TransactionVoucher::COLLECTION_STATUS_SUCCESS,
                    'order_no'          => $platform_order_no,
                    'collection_time'   => date('Y-m-d H:i:s'),
                ]) > 0;
    }
}
