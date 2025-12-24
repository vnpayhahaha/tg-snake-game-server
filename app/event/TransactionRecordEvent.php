<?php

namespace app\event;

use app\constants\DisbursementOrder;
use app\constants\TenantAccount;
use app\constants\TransactionRecord;
use app\model\ModelTransactionRecord;
use app\repository\DisbursementOrderRepository;
use app\repository\TransactionQueueStatusRepository;
use app\repository\TransactionRecordRepository;
use support\Container;
use support\Log;
use Webman\Event\Event;

class TransactionRecordEvent
{
    public function Created(ModelTransactionRecord $model): void
    {
        var_dump('ModelTransactionRecord Created event==');
        /** @var TransactionQueueStatusRepository $transactionQueueStatusRepository */
        $transactionQueueStatusRepository = Container::make(TransactionQueueStatusRepository::class);
        $isAdded = $transactionQueueStatusRepository->addQueue($model->id, $model->transaction_no, $model->transaction_type);
        if (!$isAdded) {
            Log::error("TransactionRecordEvent  => Created  filed");
        }
        $newTR = Container::make(TransactionRecordRepository::class);
        $newTR->getModel()->where('id', $model->id)->update(
            [
                'transaction_status' => TransactionRecord::STATUS_PROCESSING,
            ]
        );
    }

    public function Failed(int $transactionRecordID): void
    {
        $model = ModelTransactionRecord::find($transactionRecordID);
        var_dump('ModelTransactionRecord  Failed event==', $model->toArray());
        if ($model->account_type === TenantAccount::ACCOUNT_TYPE_PAY &&
            $model->transaction_type === TransactionRecord::TYPE_ORDER_TRANSACTION &&
            $model->transaction_status === TransactionRecord::STATUS_FAIL
        ) {
            // 订单失败
            /** @var DisbursementOrderRepository $disbursementOrderRepository */
            $disbursementOrderRepository = Container::make(DisbursementOrderRepository::class);
            $isUpdated = $disbursementOrderRepository->getModel()->where('id', $model->order_id)->update([
                'status'        => DisbursementOrder::STATUS_FAIL,
                'error_message' => $model->failed_msg,
            ]);
            if ($isUpdated) {
                // 更新订单状态
                Event::dispatch('disbursement-order-status-records', [
                    'order_id' => $model->order_id,
                    'status'   => DisbursementOrder::STATUS_FAIL,
                    'desc_cn'  => '扣款失败，创建订单失败：' . $model->failed_msg,
                    'desc_en'  => 'Failed to create order: ' . $model->failed_msg,
                    'remark'   => json_encode($model->toArray(), JSON_UNESCAPED_UNICODE),
                ]);
            }
        }

    }

    public function Success(int $transactionRecordID): void
    {
        $model = ModelTransactionRecord::find($transactionRecordID);
        var_dump('ModelTransactionRecord  Success event==', $model->toArray());
        if ($model->account_type === TenantAccount::ACCOUNT_TYPE_PAY &&
            $model->transaction_type === TransactionRecord::TYPE_ORDER_TRANSACTION &&
            $model->transaction_status === TransactionRecord::STATUS_SUCCESS
        ) {
            // 代付订单扣款成功，订单状态改为[已创建]
            /** @var DisbursementOrderRepository $disbursementOrderRepository */
            $disbursementOrderRepository = Container::make(DisbursementOrderRepository::class);
            $isUpdated = $disbursementOrderRepository->getModel()->where('id', $model->order_id)->update([
                'status' => DisbursementOrder::STATUS_CREATED,
            ]);
            if ($isUpdated) {
                // 创建代付订单成功
                Event::dispatch('disbursement-order-status-records', [
                    'order_id' => $model->order_id,
                    'status'   => DisbursementOrder::STATUS_CREATED,
                    'desc_cn'  => '扣款成功，创建订单成功',
                    'desc_en'  => 'Deduction successful, order creation successful',
                    'remark'   => json_encode($model->toArray(), JSON_UNESCAPED_UNICODE),
                ]);
                // 判断租户账户是否开启自动分配
                Event::dispatch('app.tenant.auto_assign', [
                    'tenant_id' => $model->tenant_id,
                    'order_id'  => $model->order_id,
                ]);
            }
        }
    }
}
