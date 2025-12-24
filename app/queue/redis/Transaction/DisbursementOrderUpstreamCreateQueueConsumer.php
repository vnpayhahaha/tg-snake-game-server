<?php

namespace app\queue\redis\Transaction;

use app\constants\DisbursementOrder;
use app\constants\DisbursementOrderUpstreamCreateQueue;
use app\constants\Tenant;
use app\repository\ChannelAccountRepository;
use app\repository\DisbursementOrderUpstreamCreateQueueRepository;
use app\service\DisbursementOrderService;
use app\upstream\Handle\TransactionDisbursementOrderFactory;
use DI\Attribute\Inject;
use Exception;
use support\Log;
use Webman\Event\Event;
use Webman\RedisQueue\Consumer;

class DisbursementOrderUpstreamCreateQueueConsumer implements Consumer
{
    // 要消费的队列名
    public string $queue = DisbursementOrderUpstreamCreateQueue::CONSUMER_QUEUE_NAME;

    // 连接名，对应 plugin/webman/redis-queue/redis.php 里的连接
    public string $connection = 'default';

    #[Inject]
    protected DisbursementOrderUpstreamCreateQueueRepository $queueRepository;

    #[Inject]
    protected DisbursementOrderService $disbursementOrderService;

    #[Inject]
    protected ChannelAccountRepository $channelAccountRepository;

    /**
     * 消费队列数据
     * 数据格式：
     * [
     *   'queue_id' => int,  // 队列记录ID
     * ]
     */
    public function consume($data): void
    {
        if (!isset($data['queue_id'])) {
            Log::error('DisbursementOrderUpstreamCreateQueueConsumer: 缺少queue_id参数', $data);
            return;
        }

        $queueId = $data['queue_id'];
        Log::info("DisbursementOrderUpstreamCreateQueueConsumer: 开始处理队列ID: {$queueId}");

        try {
            // 获取队列记录
            $queueItem = $this->queueRepository->findById($queueId);
            if (!$queueItem) {
                Log::error("DisbursementOrderUpstreamCreateQueueConsumer: 队列记录不存在, ID: {$queueId}");
                return;
            }

            // 检查状态是否可以处理
            if ($queueItem->process_status !== DisbursementOrderUpstreamCreateQueue::PROCESS_STATUS_WAIT) {
                Log::warning("DisbursementOrderUpstreamCreateQueueConsumer: 队列记录状态不是待处理, ID: {$queueId}, 状态: {$queueItem->process_status}");
                return;
            }

            // 更新状态为处理中
            $updateResult = $this->queueRepository->updateProcessStatus(
                $queueId,
                DisbursementOrderUpstreamCreateQueue::PROCESS_STATUS_PROCESSING
            );

            if (!$updateResult) {
                Log::warning("DisbursementOrderUpstreamCreateQueueConsumer: 更新状态失败（可能由于乐观锁冲突），队列ID: {$queueId}");
                return;
            }

            // 获取代付订单
            $disbursementOrder = $this->disbursementOrderService->repository->findById($queueItem->disbursement_order_id);
            if (!$disbursementOrder) {
                $this->handleError($queueId, 'ORDER_NOT_FOUND', '代付订单不存在');
                return;
            }

            // 检查订单状态
            if ($disbursementOrder->status !== DisbursementOrder::STATUS_ALLOCATED) {
                $this->handleError($queueId, 'ORDER_STATUS_INVALID', '订单状态不是已分配状态');
                return;
            }
            var_dump('调用上游第三方接口创建订单====', $queueItem->channel_account_id);
            // 调用上游第三方接口创建订单
            $result = $this->createUpstreamOrder($queueItem, $disbursementOrder);
            var_dump('上游返回结果==', $result);
            Log::info("DisbursementOrderUpstreamCreateQueueConsumer: 上游返回结果==", $result);
            if ($result['success']) {
                // 处理成功，使用乐观锁更新状态
                $updateResult = $this->queueRepository->updateProcessStatus(
                    $queueId,
                    DisbursementOrderUpstreamCreateQueue::PROCESS_STATUS_SUCCESS,
                    [
                        'upstream_order_no' => $result['upstream_order_no'] ?? '',
                        'upstream_response' => json_encode($result['response'] ?? []),
                    ]
                );

                if (!$updateResult) {
                    Log::warning("DisbursementOrderUpstreamCreateQueueConsumer: 更新成功状态失败（可能由于乐观锁冲突），队列ID: {$queueId}");
                    return;
                }

                // 更新代付订单的上游订单号
                if (isset($result['upstream_order_no']) && filled($result['upstream_order_no'])) {
                    $isUpdate = $this->disbursementOrderService->repository->updateById(
                        $queueItem->disbursement_order_id,
                        [
                            'upstream_order_no' => $result['upstream_order_no'],
                            'status'            => DisbursementOrder::STATUS_WAIT_FILL,
                        ]
                    );
                    var_dump('disbursementOrderService==STATUS_WAIT_FILL=$isUpdate===', $isUpdate);
                    if ($isUpdate) {
                        Event::dispatch('disbursement-order-status-records', [
                            'order_id' => $queueItem->disbursement_order_id,
                            'status'   => DisbursementOrder::STATUS_WAIT_FILL,
                            'desc_cn'  => $result['channel_code'] . " 商户ID[{$result['merchant_id']}]创建订单成功：" . $result['upstream_order_no'],
                            'desc_en'  => $result['channel_code'] . " Merchant ID[{$result['merchant_id']}] create order successfully:" . $result['upstream_order_no'],
                            'remark'   => $result['response'] ?? '',
                        ]);
                    }
                }

                Log::info("DisbursementOrderUpstreamCreateQueueConsumer: 处理成功, 队列ID: {$queueId}");
            } else {
                // 处理失败，进入重试逻辑
                // $this->handleError($queueId, $result['error_code'] ?? 'UNKNOWN_ERROR', $result['error_message'] ?? '未知错误');
                // 处理失败，更新订单状态 已创建， 等待重新分配
                $isUpdate = $this->disbursementOrderService->repository->updateById(
                    $queueItem->disbursement_order_id,
                    [
                        'status' => DisbursementOrder::STATUS_CREATED,
                    ]
                );
                var_dump('处理失败，更新订单状态 已创建， 等待重新分配====', $isUpdate);
                if ($isUpdate) {
                    if (isset($result['channel_code'], $result['merchant_id'])) {
                        Event::dispatch('disbursement-order-status-records', [
                            'order_id' => $queueItem->disbursement_order_id,
                            'status'   => DisbursementOrder::STATUS_CREATED,
                            'desc_cn'  => '待重新分配，' . $result['channel_code'] . " 商户ID[{$result['merchant_id']}]创建订单失败：" . $result['error_message'],
                            'desc_en'  => 'Waiting to be reallocated, ' . $result['channel_code'] . " Merchant ID[{$result['merchant_id']}] create order failed:" . $result['error_message'],
                            'remark'   => $result['response'] ?? '',
                        ]);
                    } else {
                        Event::dispatch('disbursement-order-status-records', [
                            'order_id' => $queueItem->disbursement_order_id,
                            'status'   => DisbursementOrder::STATUS_CREATED,
                            'desc_cn'  => '待重新分配，创建上游订单出现异常：' . $result['error_message'],
                            'desc_en'  => 'Waiting to be reallocated, create upstream order exception:' . $result['error_message'],
                            'remark'   => $result['response'] ?? '',
                        ]);
                    }

                }
            }
        } catch (Exception $e) {
            Log::error("DisbursementOrderUpstreamCreateQueueConsumer: 处理异常, 队列ID: {$queueId}", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $this->handleError($queueId, 'SYSTEM_ERROR', $e->getMessage());
        }
    }

    /**
     * 队列消费失败时的处理方法
     */
    public function onConsumeFailure(\Throwable $e, $package): void
    {
        Log::error('DisbursementOrderUpstreamCreateQueueConsumer: 队列消费失败', [
            'error'   => $e->getMessage(),
            'file'    => $e->getFile(),
            'line'    => $e->getLine(),
            'package' => $package
        ]);

        if (!isset($package['data']['queue_id'])) {
            Log::error('DisbursementOrderUpstreamCreateQueueConsumer: onConsumeFailure 缺少queue_id参数', $package);
            return;
        }

        $queueId = $package['data']['queue_id'];
        $maxAttempts = env('REDIS_QUEUE_MAX_ATTEMPTS', 5);

        // 使用乐观锁处理失败情况
        $maxRetries = 3;
        $retryInterval = 100; // 毫秒
        $retries = 0;

        while ($retries < $maxRetries) {
            try {
                $queueItem = $this->queueRepository->findById($queueId);
                if (!$queueItem) {
                    Log::error("DisbursementOrderUpstreamCreateQueueConsumer: onConsumeFailure 队列记录不存在, ID: {$queueId}");
                    return;
                }

                $errorCode = 'QUEUE_CONSUME_FAILURE';
                $errorMessage = $e->getMessage();

                if ($package['attempts'] < $maxAttempts) {
                    // 还可以重试，使用incrementRetryCount方法
                    $result = $this->queueRepository->incrementRetryCount($queueId, $errorCode, $errorMessage);
                    if ($result) {
                        Log::info("DisbursementOrderUpstreamCreateQueueConsumer: onConsumeFailure 重试次数已更新, 队列ID: {$queueId}, 尝试次数: {$package['attempts']}");
                        return;
                    }
                } else {
                    // 超过最大重试次数，标记为失败
                    $result = $this->queueRepository->updateProcessStatus(
                        $queueId,
                        DisbursementOrderUpstreamCreateQueue::PROCESS_STATUS_FAIL,
                        [
                            'error_code'    => $errorCode,
                            'error_message' => $errorMessage,
                        ]
                    );
                    if ($result) {
                        Log::error("DisbursementOrderUpstreamCreateQueueConsumer: onConsumeFailure 队列处理最终失败, 队列ID: {$queueId}");
                        return;
                    }
                }

                // 如果乐观锁冲突，重试
                if (!$result) {
                    $retries++;
                    usleep($retryInterval * 1000); // 转换为微秒
                    continue;
                }

                return;
            } catch (Exception $ex) {
                Log::error("DisbursementOrderUpstreamCreateQueueConsumer: onConsumeFailure 处理异常", [
                    'error'    => $ex->getMessage(),
                    'queue_id' => $queueId,
                    'retry'    => $retries
                ]);
                $retries++;
                usleep($retryInterval * 1000);
            }
        }

        Log::error("DisbursementOrderUpstreamCreateQueueConsumer: onConsumeFailure 重试次数用尽, 队列ID: {$queueId}");
    }

    /**
     * 创建上游订单
     */
    private function createUpstreamOrder($queueItem, $disbursementOrder): array
    {
        try {
            // 获取渠道账号信息
            $channelAccount = $this->channelAccountRepository->findById($queueItem->channel_account_id);
            if (!$channelAccount || !$channelAccount->channel) {
                return [
                    'success'       => false,
                    'error_code'    => 'CHANNEL_NOT_FOUND',
                    'error_message' => '渠道账号或渠道信息不存在',
                ];
            }

            $channelCode = $channelAccount->channel->channel_code;
            Log::info("DisbursementOrderUpstreamCreateQueueConsumer: 准备调用上游接口", [
                'platform_order_no' => $queueItem->platform_order_no,
                'channel_code'      => $channelCode,
                'amount'            => $queueItem->amount,
                'payee_account_no'  => $queueItem->payee_account_no,
            ]);
            $className = Tenant::$upstream_disbursement_options[$channelCode] ?? '';
            if (!filled($className)) {
                return [
                    'success'       => false,
                    'error_code'    => 'SERVICE_NOT_FOUND',
                    'error_message' => "未找到渠道 {$channelCode} 对应的服务类",
                    'channel_code'  => $channelCode,
                    'merchant_id'   => $channelAccount->merchant_id,
                ];
            }
            $isUpdateStatus = $this->disbursementOrderService->repository->getQuery()
                ->where('id', $queueItem->disbursement_order_id)
                ->where('status', DisbursementOrder::STATUS_ALLOCATED)
                ->update([
                    'status' => DisbursementOrder::STATUS_WAIT_PAY,
                ]);
            if (!$isUpdateStatus) {
                return [
                    'success'       => false,
                    'error_code'    => 'ORDER_STATUS_UPDATE_FAIL',
                    'error_message' => '更新订单状态为待支付失败，可能已被其他进程修改：' . $queueItem->platform_order_no,
                    'channel_code'  => $channelCode,
                    'merchant_id'   => $channelAccount->merchant_id,
                ];
            }
            Event::dispatch('disbursement-order-status-records', [
                'order_id' => $queueItem->disbursement_order_id,
                'status'   => DisbursementOrder::STATUS_WAIT_PAY,
                'desc_cn'  => $channelCode . " 商户ID[{$channelAccount->merchant_id}]正在创建订单",
                'desc_en'  => $channelCode . " Merchant ID[{$channelAccount->merchant_id}] is creating order",
                'remark'   => json_encode($channelAccount, JSON_UNESCAPED_UNICODE),
            ]);
            try {
                // 使用 TransactionDisbursementOrderFactory 调用上游接口
                $service = TransactionDisbursementOrderFactory::getInstance($className)->init($channelAccount);
                // 调用创建订单接口
                $createResult = $service->createOrder($disbursementOrder);
            } catch (\Throwable $e) {
                Log::error("DisbursementOrderUpstreamCreateQueueConsumer: 创建上游订单异常", [
                    'platform_order_no' => $queueItem->platform_order_no,
                    'channel_code'      => $channelCode,
                    'amount'            => $queueItem->amount,
                    'payee_account_no'  => $queueItem->payee_account_no,
                    'error'             => $e->getMessage(),
                ]);
                return [
                    'success'       => false,
                    'error_code'    => 'UPSTREAM_API_EXCEPTION_ERROR',
                    'error_message' => $e->getMessage(),
                    'channel_code'  => $channelCode,
                    'merchant_id'   => $channelAccount->merchant_id,
                ];
            }
            //     #[ArrayShape([
            //        'ok'     => 'bool',
            //        'msg'    => 'string',
            //        'origin' => 'string',
            //        'data'   => [
            //            '_upstream_order_no' => 'string',
            //        ]
            //    ])]
            if (filled($createResult)) {
                if (isset($createResult['ok']) && $createResult['ok'] === true) {
                    return [
                        'success'           => true,
                        'upstream_order_no' => $createResult['data']['_upstream_order_no'] ?? 'UP' . time() . rand(1000, 9999), // 实际应从上游接口返回
                        'response'          => $createResult['origin'],
                        'channel_code'      => $channelCode,
                        'merchant_id'       => $channelAccount->merchant_id,
                    ];
                } else {
                    return [
                        'success'           => false,
                        'upstream_order_no' => $createResult['data']['_upstream_order_no'] ?? '',
                        'response'          => $createResult['origin'],
                        'error_code'        => 'UPSTREAM_CREATE_FAILED',
                        'error_message'     => $createResult['msg'],
                        'channel_code'      => $channelCode,
                        'merchant_id'       => $channelAccount->merchant_id,
                    ];
                }

            } else {
                return [
                    'success'       => false,
                    'error_code'    => 'UPSTREAM_CREATE_FAILED',
                    'error_message' => '上游订单创建失败',
                    'channel_code'  => $channelCode,
                    'merchant_id'   => $channelAccount->merchant_id,
                ];
            }

        } catch (\Throwable $e) {
            Log::error("DisbursementOrderUpstreamCreateQueueConsumer: 调用上游接口异常", [
                'error'             => $e->getMessage(),
                'platform_order_no' => $queueItem->platform_order_no,
                'trace'             => $e->getTraceAsString(),
            ]);

            return [
                'success'       => false,
                'error_code'    => 'SERVICE_ERROR',
                'error_message' => $e->getMessage(),
            ];
        }
    }

    /**
     * 处理错误
     */
    private function handleError(int $queueId, string $errorCode, string $errorMessage): void
    {
        Log::error("DisbursementOrderUpstreamCreateQueueConsumer: 处理失败", [
            'queue_id'      => $queueId,
            'error_code'    => $errorCode,
            'error_message' => $errorMessage,
        ]);

        // 增加重试次数（含乐观锁处理）
        $retryResult = $this->queueRepository->incrementRetryCount($queueId, $errorCode, $errorMessage);
        if (!$retryResult) {
            Log::warning("DisbursementOrderUpstreamCreateQueueConsumer: 更新重试次数失败（可能由于乐观锁冲突），队列ID: {$queueId}");
        }
    }
}