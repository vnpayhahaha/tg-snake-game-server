<?php

namespace app\queue\redis\Transaction;

use app\constants\DisbursementOrder;
use app\constants\DisbursementOrderVerificationQueue;
use app\constants\TransactionVoucher;
use app\service\DisbursementOrderService;
use app\service\TransactionVoucherService;
use DI\Attribute\Inject;
use support\Db;
use support\Log;
use Webman\RedisQueue\Consumer;

class DisbursementOrderWriteOffConsumer implements Consumer
{
    // 要消费的队列名
    public string $queue = DisbursementOrder::DISBURSEMENT_ORDER_WRITE_OFF_QUEUE_NAME;

    // 连接名，对应 plugin/webman/redis-queue/redis.php 里的连接`
    public string $connection = 'default';

    #[Inject]
    protected DisbursementOrderService $disbursementOrderService;

    #[Inject]
    protected TransactionVoucherService $transactionVoucherService;

    /**
     *  [
     * 'platform_order_no' => $model->platform_order_no,
     * 'upstream_order_no' => $model->upstream_order_no,
     * 'amount'            => $model->amount,
     * 'utr'               => $model->utr,
     * 'rejection_reason'  => $model->rejection_reason,
     * 'payment_status'    => $model->payment_status,
     * 'order_data'        => $model->order_data,
     * ]
     */
    public function consume($data): void
    {
        if (!isset($data['platform_order_no']) && !isset($data['upstream_order_no'])) {
            return;
        }
        if (!isset($data['amount'], $data['utr'], $data['rejection_reason'], $data['payment_status'], $data['order_data'])) {
            return;
        }
        var_dump('DisbursementOrderWriteOffConsumer=========================start', $data);
        $order = $this->disbursementOrderService->repository->getQuery()
            ->where('platform_order_no', $data['platform_order_no'])
            ->orWhere('upstream_order_no', $data['upstream_order_no'])
            ->first();
        if (!$order) {
            // 订单不存在
            return;
        }
        $transaction_voucher_type = '';
        $transaction_voucher = '';
        if (filled($data['utr'])) {
            $transaction_voucher_type = TransactionVoucher::TRANSACTION_VOUCHER_TYPE_UTR;
            $transaction_voucher = $data['utr'];
        } elseif (isset($data['upstream_order_no']) && filled($data['upstream_order_no'])) {
            $transaction_voucher_type = TransactionVoucher::TRANSACTION_VOUCHER_TYPE_UPSTREAM_ORDER_NO;
            $transaction_voucher = $data['upstream_order_no'];
        } elseif (isset($data['platform_order_no']) && filled($data['platform_order_no'])) {
            $transaction_voucher_type = TransactionVoucher::TRANSACTION_VOUCHER_TYPE_PLATFORM_ORDER_NO;
            $transaction_voucher = $data['platform_order_no'];
        }

        if (!filled($transaction_voucher) || !filled($transaction_voucher_type)) {
            return;
        }

        if ($order->status !== DisbursementOrder::STATUS_WAIT_FILL) {
            // 或失效, 记录凭证管理
            if ($data['payment_status'] === DisbursementOrderVerificationQueue::PAY_STATUS_SUCCESS) {
                // 创建凭证管理
                $this->transactionVoucherService->create([
                    'channel_id'               => $order->disbursement_channel_id,
                    'channel_account_id'       => $order->channel_account_id,
                    'bank_account_id'          => $order->bank_account_id,
                    'collection_card_no'       => $order->payee_account_no,
                    'collection_amount'        => $data['amount'],
                    'collection_time'          => date('Y-m-d H:i:s'),
                    'collection_status'        => TransactionVoucher::COLLECTION_STATUS_FAIL,
                    'collection_source'        => TransactionVoucher::COLLECTION_SOURCE_BANK_RECEIPT,
                    'transaction_voucher'      => $transaction_voucher,
                    'transaction_voucher_type' => $transaction_voucher_type,
                    'content'                  => $data['order_data'],
                    'transaction_type'         => TransactionVoucher::TRANSACTION_TYPE_PAYMENT,
                ]);
            }
            return;
        }
        // 支付成功
        if ($data['payment_status'] === DisbursementOrderVerificationQueue::PAY_STATUS_SUCCESS) {
            // 创建凭证管理
            $tV = $this->transactionVoucherService->create([
                'channel_id'               => $order->disbursement_channel_id,
                'channel_account_id'       => $order->channel_account_id,
                'bank_account_id'          => $order->bank_account_id,
                'collection_card_no'       => $order->payee_account_no,
                'collection_amount'        => $data['amount'],
                'collection_time'          => date('Y-m-d H:i:s'),
                'collection_status'        => TransactionVoucher::COLLECTION_STATUS_WAITING,
                'collection_source'        => TransactionVoucher::COLLECTION_SOURCE_BANK_RECEIPT,
                'transaction_voucher'      => $transaction_voucher,
                'transaction_voucher_type' => $transaction_voucher_type,
                'content'                  => $data['order_data'],
                'transaction_type'         => TransactionVoucher::TRANSACTION_TYPE_PAYMENT,
            ]);
            $this->disbursementOrderService->writeOff($order->id, $tV->id);
        } else {
            // 支付失败，退款
            try {
                $this->disbursementOrderService->refund($order->id, $data['rejection_reason']);
            } catch (\Throwable $e) {
                Log::error('付款核销队列支付失败，退款异常:' . $e->getMessage(), [
                    'order_id'  => $order->id,
                    'data'      => $data,
                    'exception' => $e->getMessage(),
                    'trace'     => $e->getTraceAsString(),
                ]);
            }
        }
    }
}