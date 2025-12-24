<?php

namespace app\queue\redis\Transaction;

use app\constants\CollectionOrder;
use app\constants\TransactionVoucher;
use app\service\CollectionOrderService;
use app\service\TransactionVoucherService;
use app\tools\Base62Converter;
use DI\Attribute\Inject;
use Exception;
use support\Log;
use Webman\RedisQueue\Consumer;

class CollectionOrderWriteOffConsumer implements Consumer
{
    // 要消费的队列名
    public string $queue = CollectionOrder::COLLECTION_ORDER_WRITE_OFF_QUEUE_NAME;

    // 连接名，对应 plugin/webman/redis-queue/redis.php 里的连接`
    public string $connection = 'default';

    #[Inject]
    protected CollectionOrderService $collectionOrderService;

    #[Inject]
    protected TransactionVoucherService $transactionVoucherService;

    /**
     * @throws Exception
     */
    public function consume($data): void
    {
        $transaction_voucher_id = $data['transaction_voucher_id'];
        $transaction_voucher_type = $data['transaction_voucher_type'];
        $transaction_voucher = $data['transaction_voucher'];
        $channel_id = $data['channel_id'];
        $bank_account_id = $data['bank_account_id'];
        var_dump('CollectionOrderWriteOffConsumer===', $data);
        switch ($transaction_voucher_type) {
            case TransactionVoucher::TRANSACTION_VOUCHER_TYPE_UTR:
                $order = $this->collectionOrderService->repository->getQuery()
                    ->where('collection_channel_id', $channel_id)
                    ->where('channel_account_id', $bank_account_id)
                    ->where('customer_submitted_utr', $transaction_voucher)
                    ->where('status', CollectionOrder::STATUS_PROCESSING)
                    ->first();
                break;
            case TransactionVoucher::TRANSACTION_VOUCHER_TYPE_ORDER_ID:
                $order_id = Base62Converter::base62ToDec($transaction_voucher);
                $order = $this->collectionOrderService->repository->getQuery()
                    ->where('id', $order_id)
                    ->where('status', CollectionOrder::STATUS_PROCESSING)
                    ->first();
                break;
            case TransactionVoucher::TRANSACTION_VOUCHER_TYPE_PLATFORM_ORDER_NO:
                $order = $this->collectionOrderService->repository->getQuery()
                    ->where('platform_order_no', $transaction_voucher)
                    ->where('status', CollectionOrder::STATUS_PROCESSING)
                    ->first();
                break;
            case TransactionVoucher::TRANSACTION_VOUCHER_TYPE_AMOUNT:
                $order = $this->collectionOrderService->repository->getQuery()
                    ->where('collection_channel_id', $channel_id)
                    ->where('channel_account_id', $bank_account_id)
                    ->where('amount', $transaction_voucher)
                    ->where('status', CollectionOrder::STATUS_PROCESSING)
                    ->first();
                break;
            case TransactionVoucher::TRANSACTION_VOUCHER_TYPE_UPSTREAM_ORDER_NO:
                $order = $this->collectionOrderService->repository->getQuery()
                    ->where('upstream_order_no', $transaction_voucher)
                    ->where('status', CollectionOrder::STATUS_PROCESSING)
                    ->first();
                break;
            default:
                throw new \RuntimeException('Invalid channel type');
                break;
        }
        if (!$order) {
            $this->transactionVoucherService->updateById($transaction_voucher_id, [
                'status' => TransactionVoucher::COLLECTION_STATUS_FAIL,
            ]);
            return;
        }
        // todo 查询配置 失败百分比

        try {
            $isOk = $this->collectionOrderService->writeOff($order->id, $transaction_voucher_id);
        } catch (\Exception $e) {
            Log::warning('CollectionOrderWriteOffConsumer==', $e->getMessage());
            $isOk = false;
        }
        if (!$isOk) {
            $this->transactionVoucherService->updateById($transaction_voucher_id, [
                'status' => TransactionVoucher::COLLECTION_STATUS_FAIL,
            ]);
        }
    }

}