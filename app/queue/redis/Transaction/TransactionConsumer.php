<?php

namespace app\queue\redis\Transaction;

use app\constants\TenantAccount;
use app\constants\TransactionQueueStatus;
use app\constants\TransactionRecord;
use app\model\enums\TenantAccountRecordChangeType;
use app\model\ModelTenantAccount;
use app\model\ModelTransactionQueueStatus;
use app\model\ModelTransactionRecord;
use app\repository\DisbursementOrderRepository;
use app\repository\TenantAccountRepository;
use app\repository\TransactionRecordRepository;
use DI\Attribute\Inject;
use Exception;
use support\Db;
use support\Log;
use Webman\Event\Event;
use Webman\RedisQueue\Consumer;

class TransactionConsumer implements Consumer
{
    // 要消费的队列名
    public string $queue = TenantAccount::TRANSACTION_CONSUMER_QUEUE_NAME;

    // 连接名，对应 plugin/webman/redis-queue/redis.php 里的连接`
    public string $connection = 'default';

    #[Inject]
    protected TenantAccountRepository $tenantAccountRepository;
    #[Inject]
    protected TransactionRecordRepository $transactionRecordRepository;
    #[Inject]
    protected DisbursementOrderRepository $disbursementOrderRepository;


    // 消费
    public function consume($data)
    {
        // 'id'   => $transaction_id,
        // 'transaction_no'   => $transaction_no,
        // 'transaction_type' => $transaction_type,
        dump('bill_change_consumer == run ==============================');
        Log::warning('bill_change_consumer == run =======================');

        /** @var ModelTransactionRecord $transaction_record */
        $transaction_record = $this->transactionRecordRepository->getQuery()->where('id', $data['id'])->firstOrFail();
        // 判断 expected_settlement_time 是否满足
        if (strtotime($transaction_record->expected_settlement_time) > time()) {
            return false;
        }
        if ($transaction_record->transaction_status > TransactionRecord::STATUS_PROCESSING) {
            return false;
        }

        $tenant_account = ModelTenantAccount::query()
            ->where('tenant_id', $transaction_record->tenant_id)
            ->where('account_type', $transaction_record->account_type)
            ->firstOrFail();

        $maxRetries = 3;
        $retryInterval = 100; // 毫秒
        $retries = 0;
        while ($retries < $maxRetries) {
            Db::beginTransaction();
            try {
                /** @var ModelTransactionQueueStatus $transaction_queue */
                $transaction_queue = ModelTransactionQueueStatus::query()->where('transaction_no', $data['transaction_no'])->firstOrFail();
                if ($transaction_queue->process_status === TransactionQueueStatus::STATUS_SUCCESS || $transaction_queue->process_status === TransactionQueueStatus::STATUS_FAIL) {
                   throw new \RuntimeException('队列已处理完成');
                }
                $lock_version = $transaction_queue->lock_version;
                // 根据 transaction_type 计算
                Log::info('transaction_type: ' . $transaction_record->transaction_type);
                switch ($transaction_record->transaction_type) {
                    case TransactionRecord::TYPE_ORDER_TRANSACTION:
                        Log::info('订单交易', [$transaction_record]);
                        $this->tenantAccountRepository->updateBalanceAvailableById($tenant_account->id, $transaction_record->net_amount, TenantAccountRecordChangeType::CHANGE_TYPE_TRANSACTION, $transaction_record->transaction_no);
                        break;
                    case TransactionRecord::TYPE_ORDER_REFUND:
                        var_dump('订单退款');
                        $this->tenantAccountRepository->updateBalanceAvailableById($tenant_account->id, $transaction_record->net_amount, TenantAccountRecordChangeType::CHANGE_TYPE_REFUND, $transaction_record->transaction_no);
                        break;
                    case TransactionRecord::TYPE_MANUAL_ADD:
                        var_dump('资金调增');
                        $this->tenantAccountRepository->updateBalanceAvailableById($tenant_account->id, $transaction_record->net_amount, TenantAccountRecordChangeType::CHANGE_TYPE_MANUAL_ADD, $transaction_record->transaction_no);
                        break;
                    case TransactionRecord::TYPE_MANUAL_SUB:
                        var_dump('资金调减');
                        $this->tenantAccountRepository->updateBalanceAvailableById($tenant_account->id, $transaction_record->net_amount, TenantAccountRecordChangeType::CHANGE_TYPE_MANUAL_SUB, $transaction_record->transaction_no);
                        break;
                    case TransactionRecord::TYPE_FREEZE:
                        var_dump('冻结资金');
                        $this->tenantAccountRepository->updateBalanceFrozenById($tenant_account->id, $transaction_record->net_amount, TenantAccountRecordChangeType::CHANGE_TYPE_FREEZE, $transaction_record->transaction_no);
                        break;
                    case TransactionRecord::TYPE_UNFREEZE:
                        var_dump('解冻资金');
                        $this->tenantAccountRepository->updateBalanceFrozenById($tenant_account->id, $transaction_record->net_amount, TenantAccountRecordChangeType::CHANGE_TYPE_UNFREEZE, $transaction_record->transaction_no);
                        break;
                    case TransactionRecord::TYPE_TRANSFER_RECEIVE_TO_PAY:
                        var_dump('收转付');
                        break;
                    case TransactionRecord::TYPE_TRANSFER_PAY_TO_RECEIVE:
                        var_dump('付转收');
                        break;
                    case TransactionRecord::TYPE_REVERSE:
                        var_dump('冲正交易');
                        break;
                    case TransactionRecord::TYPE_ERROR_ADJUST:
                        var_dump('差错调整');
                        break;
                    default:
                        throw new \RuntimeException('未知的交易类型：' . $transaction_record->transaction_type);
                }

                $update_transaction_queue = [
                    'scheduled_execute_time' => date('Y-m-d H:i:s'),
                    'process_status'         => TransactionQueueStatus::STATUS_SUCCESS,
                    'lock_version'           => $lock_version + 1,
                ];
                // 更新 transaction_record 状态 成功
                $updateOk = $this->transactionRecordRepository->getModel()->where('transaction_no', $data['transaction_no'])->update(
                    [
                        'transaction_status'     => TransactionRecord::STATUS_SUCCESS,
                        'actual_settlement_time' => date('Y-m-d H:i:s'),
                    ]
                );
                Log::warning('更新 transaction_record 状态成功====', [$updateOk]);
                if ($updateOk) {
                    Event::dispatch('app.transaction.success', $data['id']);
                }
                // 执行乐观锁更新
                $updateResult = ModelTransactionQueueStatus::query()
                    ->where('transaction_no', $data['transaction_no'])
                    ->where('lock_version', $lock_version)
                    ->update($update_transaction_queue);
                if ($updateResult === 0) {
                    throw new \RuntimeException("Concurrent modification detected", 409);
                }
                Db::commit();
                return $updateResult;
            } catch (\Throwable $e) {
                Db::rollback();
                if ($e->getCode() === 409) {
                    $retries++;
                    usleep($retryInterval * 1000); // 转换为微秒
                    continue;
                }
                var_dump('消费失败：', $e->getMessage());
                throw $e;
            }
        }
        echo "bill_change_consumer done\n";
        return false;
    }

    public function onConsumeFailure(\Throwable $e, $package)
    {
        echo "bill_change_consumer failure\n";
        echo $e->getMessage() . "\n";
        echo 'file：  ' . $e->getFile() . "\n";
        echo 'line：  ' . $e->getLine() . "\n";
        // 无需反序列化
        var_export($package);
        //   'max_attempts'  => env('REDIS_QUEUE_MAX_ATTEMPTS', 5),
        //  'retry_seconds' => env('REDIS_QUEUE_RETRY_SECONDS', 10),
        $max_attempts = env('REDIS_QUEUE_MAX_ATTEMPTS', 5);
        $retry_seconds = env('REDIS_QUEUE_RETRY_SECONDS', 10);
        if (isset($package['data']['transaction_no']) && filled($package['data']['transaction_no'])) {
            $maxRetries = 3;
            $retryInterval = 100; // 毫秒
            $retries = 0;
            while ($retries < $maxRetries) {
                try {
                    var_dump('transaction===');
                    return Db::transaction(function () use ($e, $package, $max_attempts, $retry_seconds) {
                        var_dump('transaction===start==');
                        /** @var ModelTransactionQueueStatus $transaction_queue */
                        $transaction_queue = ModelTransactionQueueStatus::query()->where('transaction_no', $package['data']['transaction_no'])->firstOrFail();
                        $lock_version = $transaction_queue->lock_version;
                        $update_transaction_queue = [
                            'scheduled_execute_time' => date('Y-m-d H:i:s'),
                            'retry_count'            => $package['attempts'],
                            'error_code'             => $package['error'] ?? $e->getMessage(),
                            'error_detail'           => $package['data'] ? json_encode($package['data']) : '',
                            'lock_version'           => $lock_version + 1,
                        ];
                        if ($package['attempts'] < $max_attempts) {
                            $update_transaction_queue['next_retry_time'] = date('Y-m-d H:i:s', strtotime("+{$retry_seconds} seconds"));
                        } else {
                            $update_transaction_queue['process_status'] = TransactionQueueStatus::STATUS_FAIL;
                            $updateOk = $this->transactionRecordRepository->getModel()->where('id', $package['data']['id'])->update(
                                [
                                    'transaction_status' => TransactionRecord::STATUS_FAIL,
                                    'failed_msg'         => $package['error'],
                                ]
                            );
                            if ($updateOk) {
                                Event::dispatch('app.transaction.failed', $package['data']['id']);
                            }

                        }
                        // 执行乐观锁更新
                        $updateResult = ModelTransactionQueueStatus::query()
                            ->where('transaction_no', $package['data']['transaction_no'])
                            ->where('lock_version', $lock_version)
                            ->update($update_transaction_queue);
                        if ($updateResult === 0) {
                            throw new \RuntimeException("Concurrent modification detected", 409);
                        }
                        return $updateResult;
                    });

                } catch (Exception $e) {
                    if ($e->getCode() === 409) {
                        $retries++;
                        usleep($retryInterval * 1000); // 转换为微秒
                        continue;
                    }
                    var_dump('transaction===failed', $e->getMessage());
                    throw $e;
                }
            }
        }
    }
}
