<?php

namespace app\queue\redis\Transaction;

use app\constants\DisbursementOrder;
use app\service\DisbursementOrderService;
use DI\Attribute\Inject;
use support\Log;
use Webman\RedisQueue\Consumer;

class DisbursementOrderRefundConsumer implements Consumer
{
    // 要消费的队列名
    public string $queue = DisbursementOrder::DISBURSEMENT_ORDER_REFUND_QUEUE_NAME;

    // 连接名，对应 plugin/webman/redis-queue/redis.php 里的连接`
    public string $connection = 'default';

    #[Inject]
    protected DisbursementOrderService $disbursementOrderService;

    public function consume($data): void
    {
        if (!isset($data['ids'], $data['refund_reason']) || !filled($data['ids'])) {
            return;
        }
        if (is_array($data['ids'])) {
            foreach ($data['ids'] as $id) {
                // 查询判断订单状态
                $disbursementOrder = $this->disbursementOrderService->findById($id);
                if (!$disbursementOrder) {
                    continue;
                }
                if ($disbursementOrder->status === DisbursementOrder::AdjustToFailure) {
                    continue;
                }
                $this->disbursementOrderService->refund($id, $data['refund_reason']);
            }
        }
    }

    public function onConsumeFailure(\Throwable $e, $package)
    {
        Log::error('DisbursementOrderRefundConsumer  error:', $e->getMessage());
        return;
    }
}