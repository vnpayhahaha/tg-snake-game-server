<?php

namespace app\queue\redis\Notice;

use app\constants\TenantAccount;
use app\constants\TenantNotificationQueue;
use app\constants\TenantNotificationRecord;
use app\model\ModelTenantNotificationQueue;
use app\repository\CollectionOrderRepository;
use app\repository\DisbursementOrderRepository;
use app\repository\TenantNotificationQueueRepository;
use app\repository\TenantNotificationRecordRepository;
use DI\Attribute\Inject;
use GuzzleHttp\Client;
use support\Db;
use support\Log;
use Webman\RedisQueue\Consumer;
use Webman\RedisQueue\Redis;

class TenantNoticeConsumer implements Consumer
{
    // 要消费的队列名
    public string $queue = TenantNotificationQueue::TENANT_NOTIFICATION_QUEUE_NAME;

    // 连接名，对应 plugin/webman/redis-queue/redis.php 里的连接`
    public string $connection = 'default';

    #[Inject]
    protected TenantNotificationQueueRepository $tenantNotificationQueueRepository;
    #[Inject]
    protected TenantNotificationRecordRepository $tenantNotificationRecordRepository;

    #[Inject]
    public CollectionOrderRepository $collectionOrderRepository;
    #[Inject]
    public DisbursementOrderRepository $disbursementOrderRepository;

    // 消费

    /**
     * @param $data [
     * 'id'              => $model->id,
     * 'tenant_id'             => $model->tenant_id,
     * 'app_id'                => $model->app_id,
     * 'account_type'          => $model->account_type,
     * 'disbursement_order_id' => $model->disbursement_order_id,
     * 'notification_type'     => $model->notification_type,
     * 'notification_url'      => $model->notification_url,
     * 'request_method'        => $model->request_method,
     * 'request_data'          => $model->request_data,
     * 'max_retry_count'       => $model->max_retry_count,
     * ]
     * @return void
     */
    public function consume($data)
    {
        $queueId = $data['id'] ?? null;
        if (!$queueId) {
            Log::error('TenantNoticeConsumer: queue_id is missing', ['data' => $data]);
            return;
        }

        // 参考TransactionConsumer实现重试机制
        $maxRetries = 3;
        $retryInterval = 100; // 毫秒
        $retries = 0;

        $currentExecuteNum = 1;

        $queueModel = $this->tenantNotificationQueueRepository->findById($queueId);
        if (!$queueModel) {
            Log::error('TenantNoticeConsumer: queue record not found', ['queue_id' => $queueId]);
            return;
        }
        // 使用乐观锁机制获取队列记录
        while ($retries < $maxRetries) {
            Db::beginTransaction();
            try {


                // 检查队列状态，避免重复处理
                if ($queueModel->execute_status === TenantNotificationQueue::EXECUTE_STATUS_SUCCESS) {
                    Log::info('TenantNoticeConsumer: queue already processed successfully', ['queue_id' => $queueId]);
                    Db::rollBack();
                    return;
                }

                // 检查是否已经达到最大重试次数
                if ($queueModel->execute_count >= $queueModel->max_retry_count) {
                    Log::error('TenantNoticeConsumer: max retry count reached', ['queue_id' => $queueId]);
                    Db::commit();
                    return;
                }

                // 获取乐观锁版本号
                $lockVersion = $queueModel->lock_version;

                // 更新执行次数和状态
                $currentExecuteNum = $queueModel->execute_count + 1;
                $execute_status = TenantNotificationQueue::EXECUTE_STATUS_EXECUTING;
                $last_execute_time = date('Y-m-d H:i:s');

                // 执行乐观锁更新
                $updateResult = $this->tenantNotificationQueueRepository->getModel()
                    ->where('id', $queueModel->id)
                    ->where('lock_version', $lockVersion)
                    ->update([
                        'execute_count'     => $currentExecuteNum,
                        'execute_status'    => $execute_status,
                        'last_execute_time' => $last_execute_time,
                        'lock_version'      => $lockVersion + 1,
                        'updated_at'        => date('Y-m-d H:i:s')
                    ]);

                if ($updateResult === 0) {
                    // 乐观锁更新失败，出现并发修改
                    throw new \RuntimeException("Concurrent modification detected", 409);
                }

                Db::commit();
                break; // 成功更新状态，跳出重试循环

            } catch (\Throwable $e) {
                Db::rollBack();
                if ($e->getCode() === 409) {
                    // 乐观锁冲突，进行重试
                    $retries++;
                    usleep($retryInterval * 1000); // 转换为微秒
                    continue;
                }
                // 其他异常直接抛出
                throw $e;
            }
        }
        $queueData = $queueModel->toArray();
        // 执行第三方请求
        try {

            // 发起第三方请求
            $response = $this->sendNotification($queueData);

            // 检查响应是否成功（状态码200且响应内容为'ok'或'success'）
            $responseBody = trim(strtolower($response));
            if (strtolower($responseBody) === 'ok' || strtolower($responseBody) === 'success') {
                // 记录成功日志
                $this->recordNotificationLog($queueData, 200, $response, TenantNotificationRecord::STATUS_SUCCESS, $currentExecuteNum);

                // 更新队列状态为成功
                $this->updateQueueStatusFinal($queueId, TenantNotificationQueue::EXECUTE_STATUS_SUCCESS, 'The notification was successful');

                Log::info('TenantNoticeConsumer: notification sent successfully', ['queue_id' => $queueId]);
            } else {
                // 响应内容不符合要求，视为失败
                $errorMessage = "Invalid response content: " . $response;
                // 抛出异常触发重试机制
                throw new \RuntimeException($errorMessage);
            }

        } catch (\Throwable $e) {
            // 记录失败日志
            $this->recordNotificationLog($queueData, $e->getCode() ?:
                500, $e->getMessage(), TenantNotificationRecord::STATUS_FAIL, $currentExecuteNum);

            // 更新队列状态为失败，并设置下次执行时间
            $this->updateQueueStatusWithRetry($queueId, $e->getMessage(), $queueData['max_retry_count'] ?? 3);

            Log::error('TenantNoticeConsumer: notification failed', [
                'queue_id' => $queueId,
                'error'    => $e->getMessage()
            ]);

            // 抛出异常触发重试机制
            throw $e;
        }
    }

    /**
     * 发送通知到第三方接口
     *
     * @param array $data
     * @return string
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function sendNotification(array $data): string
    {
        $client = new Client();

        $options = [
            'timeout' => 30,
            'headers' => [
                'Content-Type' => 'application/json',
            ],
        ];

        // 根据请求方法设置请求参数
        if (!empty($data['request_data'])) {
            if (isset($data['request_method']) && strtoupper($data['request_method']) === 'GET') {
                $options['query'] = $data['request_data'];
            } else {
                $options['json'] = $data['request_data'];
            }
        }

        $response = $client->request($data['request_method'] ?? 'POST', $data['notification_url'] ?? '', $options);

        return $response->getBody()->getContents();
    }

    /**
     * 记录通知日志
     *
     * @param array $requestData
     * @param int $statusCode
     * @param string $response
     * @param int $status
     * @return void
     */
    private function recordNotificationLog(array $requestData, int $statusCode, string $response, int $status, int $currentExecuteNum = 1): void
    {
        $logData = [
            'queue_id'              => $requestData['id'] ?? null,
            'tenant_id'             => $requestData['tenant_id'] ?? '',
            'app_id'                => $requestData['app_id'] ?? '',
            'account_type'          => $requestData['account_type'] ?? 0,
            'collection_order_id'   => $requestData['collection_order_id'] ?? 0,
            'disbursement_order_id' => $requestData['disbursement_order_id'] ?? 0,
            'notification_type'     => $requestData['notification_type'] ?? 0,
            'notification_url'      => $requestData['notification_url'] ?? '',
            'request_method'        => $requestData['request_method'] ?? 'POST',
            'request_data'          => isset($requestData['request_data']) ?
                json_encode($requestData['request_data'], JSON_THROW_ON_ERROR) : '',
            'response_status'       => $statusCode,
            'response_data'         => $response,
            'execute_count'         => $currentExecuteNum,
            'status'                => $status,
            'created_at'            => date('Y-m-d H:i:s'),
            'updated_at'            => date('Y-m-d H:i:s')
        ];

        // 获取当前队列的执行次数
        if (!empty($requestData['id'])) {
            $queueModel = $this->tenantNotificationQueueRepository->findById($requestData['id']);
            if ($queueModel) {
                $logData['execute_count'] = $queueModel->execute_count;
            }
        }

        $this->tenantNotificationRecordRepository->create($logData);
    }

    /**
     * 更新队列状态（处理中状态更新）
     *
     * @param ModelTenantNotificationQueue $queueModel
     * @param int $status
     * @param string $errorMessage
     * @return void
     */
    private function updateQueueStatus(ModelTenantNotificationQueue $queueModel, int $status, string $errorMessage): void
    {
        $updateData = [
            'execute_status' => $status,
            'error_message'  => $errorMessage,
            'updated_at'     => date('Y-m-d H:i:s')
        ];

        // 如果是成功状态，记录完成时间
        if ($status === TenantNotificationQueue::EXECUTE_STATUS_SUCCESS) {
            $updateData['next_execute_time'] = null;
        }

        $this->tenantNotificationQueueRepository->updateById($queueModel->id, $updateData);
    }

    /**
     * 更新队列最终状态
     *
     * @param int $queueId
     * @param int $status
     * @param string $remark
     * @return void
     */
    private function updateQueueStatusFinal(int $queueId, int $status, string $remark = ''): void
    {
        $updateData = [
            'execute_status' => $status,
            'error_message'  => $status === TenantNotificationQueue::EXECUTE_STATUS_FAILURE ? $remark : null,
            'updated_at'     => date('Y-m-d H:i:s')
        ];

        // 如果是成功状态，清除下次执行时间
        if ($status === TenantNotificationQueue::EXECUTE_STATUS_SUCCESS) {
            $updateData['next_execute_time'] = null;
        }

        $isUpdate = $this->tenantNotificationQueueRepository->updateById($queueId, $updateData);
        // 同步订单的通知状态
        if ($isUpdate) {
            $this->syncOrderNotificationStatus($queueId);
        }
    }

    // 同步订单的通知状态
    private function syncOrderNotificationStatus(int $queueId): void
    {
        $queueFind = $this->tenantNotificationQueueRepository->findById($queueId);
        if (!$queueFind) {
            return;
        }
        if ($queueFind->account_type === TenantAccount::ACCOUNT_TYPE_RECEIVE && $queueFind->notification_type === TenantNotificationQueue::NOTIFICATION_TYPE_ORDER && $queueFind->collection_order_id > 0) {
            $this->collectionOrderRepository->updateById($queueFind->collection_order_id, [
                'notify_status' => $queueFind->execute_status,
                'updated_at'    => date('Y-m-d H:i:s')
            ]);
        }
        if ($queueFind->account_type === TenantAccount::ACCOUNT_TYPE_PAY && $queueFind->notification_type === TenantNotificationQueue::NOTIFICATION_TYPE_ORDER && $queueFind->disbursement_order_id > 0) {
            $this->disbursementOrderRepository->updateById($queueFind->disbursement_order_id, [
                'notify_status' => $queueFind->execute_status,
                'updated_at'    => date('Y-m-d H:i:s')
            ]);
        }
    }

    /**
     * 更新队列状态并设置重试时间
     *
     * @param int $queueId
     * @param string $errorMessage
     * @param int $maxRetryCount
     * @return void
     */
    private function updateQueueStatusWithRetry(int $queueId, string $errorMessage, int $maxRetryCount): void
    {
        // 获取当前队列信息
        $queueModel = $this->tenantNotificationQueueRepository->findById($queueId);
        if (!$queueModel) {
            return;
        }

        // 计算下次执行时间（指数退避算法：10秒、20秒、40秒...）
        $executeCount = $queueModel->execute_count;
        $baseDelay = 10; // 基础延迟时间（秒）
        $delaySeconds = $baseDelay * (2 ** ($executeCount - 1)); // 指数退避

        $updateData = [
            'execute_status'    => TenantNotificationQueue::EXECUTE_STATUS_FAILURE,
            'error_message'     => $errorMessage,
            'last_execute_time' => date('Y-m-d H:i:s'),
            'updated_at'        => date('Y-m-d H:i:s')
        ];

        // 如果还没达到最大重试次数，设置下次执行时间
        dump('如果还没达到最大重试次数，设置下次执行时间==', $executeCount, $maxRetryCount);
        if ($executeCount < $maxRetryCount) {
            $updateData['next_execute_time'] = date('Y-m-d H:i:s', time() + $delaySeconds);
        } else {
            // 达到最大重试次数，清除下次执行时间
            $updateData['next_execute_time'] = null;

        }

        $this->tenantNotificationQueueRepository->updateById($queueId, $updateData);
        if ($executeCount < $maxRetryCount) {
            // 重新入队列，并计算设置延迟时间
            Redis::send(TenantNotificationQueue::TENANT_NOTIFICATION_QUEUE_NAME, [
                'id'                    => $queueId,
                'tenant_id'             => $queueModel->tenant_id,
                'app_id'                => $queueModel->app_id,
                'account_type'          => $queueModel->account_type,
                'disbursement_order_id' => $queueModel->disbursement_order_id,
                'notification_type'     => $queueModel->notification_type,
                'notification_url'      => $queueModel->notification_url,
                'request_method'        => $queueModel->request_method,
                'request_data'          => $queueModel->request_data,
                'max_retry_count'       => $queueModel->max_retry_count,
            ], $delaySeconds - 1);
        } else {
            // 同步订单的通知状态
            $this->syncOrderNotificationStatus($queueId);
        }


    }

    /**
     * 消费失败处理
     *
     * @param \Throwable $e
     * @param $package
     * @return void
     */
    public function onConsumeFailure(\Throwable $e, $package)
    {
        dump('TenantNoticeConsumer===========onConsumeFailure=====', $e, $package);

        $data = $package['data'] ?? [];
        $queueId = $data['id'] ?? null;

        if ($queueId) {
            // 记录错误信息
            $this->tenantNotificationQueueRepository->updateById($queueId, [
                'error_message' => $e->getMessage(),
            ]);
        }
        Log::error('TenantNoticeConsumer consume failure', [
            'exception' => $e->getMessage(),
            'data'      => $data
        ]);
    }
}