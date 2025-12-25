<?php

namespace app\process\task;

use app\constants\TgPrizeDispatchQueue as QueueConst;
use app\repository\TgPrizeDispatchQueueRepository;
use app\service\TgPrizeTransferService;
use DI\Attribute\Inject;
use support\Log;
use Workerman\Crontab\Crontab;

/**
 * 奖金派发队列处理进程
 * 定时处理待派发的奖金转账任务
 */
class PrizeDispatchQueueProcess
{
    #[Inject]
    protected TgPrizeDispatchQueueRepository $queueRepository;

    #[Inject]
    protected TgPrizeTransferService $transferService;

    public function onWorkerStart(): void
    {
        Log::info("PrizeDispatchQueueProcess: 进程启动");

        // 每10秒处理一次派发队列
        new Crontab('*/10 * * * * *', function() {
            try {
                $this->processPendingTasks();
            } catch (\Throwable $e) {
                Log::error("PrizeDispatchQueueProcess处理待处理任务失败: " . $e->getMessage(), [
                    'trace' => $e->getTraceAsString()
                ]);
            }
        });

        // 每30秒检查处理中的超时任务
        new Crontab('*/30 * * * * *', function() {
            try {
                $this->handleTimeoutTasks();
            } catch (\Throwable $e) {
                Log::error("PrizeDispatchQueueProcess处理超时任务失败: " . $e->getMessage(), [
                    'trace' => $e->getTraceAsString()
                ]);
            }
        });

        // 每分钟重试失败的任务
        new Crontab('0 * * * * *', function() {
            try {
                $this->retryFailedTasks();
            } catch (\Throwable $e) {
                Log::error("PrizeDispatchQueueProcess重试失败任务失败: " . $e->getMessage(), [
                    'trace' => $e->getTraceAsString()
                ]);
            }
        });

        Log::info("PrizeDispatchQueueProcess: 所有Crontab已设置");
    }

    /**
     * 处理待处理的任务
     */
    protected function processPendingTasks(): void
    {
        // 获取待处理的任务（按优先级排序）
        $tasks = $this->queueRepository->getPendingTasks(10);

        if ($tasks->isEmpty()) {
            Log::debug("PrizeDispatchQueueProcess: 没有待处理的任务");
            return;
        }

        Log::info("PrizeDispatchQueueProcess: 开始处理 {$tasks->count()} 个待处理任务");

        foreach ($tasks as $task) {
            try {
                // 使用乐观锁标记为处理中
                $updated = $this->queueRepository->updateWithVersion($task->id, [
                    'status' => QueueConst::STATUS_PROCESSING,
                    'process_start_time' => now(),
                    'retry_count' => $task->retry_count + 1,
                ], $task->version);

                if (!$updated) {
                    Log::debug("任务 {$task->id} 已被其他进程处理");
                    continue;
                }

                // 执行转账
                $result = $this->transferService->executeTransfer(
                    $task->prize_record_id,
                    $task->winner_address,
                    $task->transfer_amount
                );

                if ($result['success']) {
                    // 转账成功，标记为完成
                    $this->queueRepository->updateById($task->id, [
                        'status' => QueueConst::STATUS_COMPLETED,
                        'process_end_time' => now(),
                        'tx_hash' => $result['tx_hash'] ?? null,
                        'error_message' => null,
                    ]);

                    Log::info("任务 {$task->id} 处理成功", [
                        'prize_record_id' => $task->prize_record_id,
                        'winner_address' => $task->winner_address,
                        'amount' => $task->transfer_amount,
                        'tx_hash' => $result['tx_hash'] ?? null,
                    ]);
                } else {
                    // 转账失败，标记为失败
                    $this->queueRepository->updateById($task->id, [
                        'status' => QueueConst::STATUS_FAILED,
                        'process_end_time' => now(),
                        'error_message' => $result['message'] ?? '转账失败',
                    ]);

                    Log::error("任务 {$task->id} 处理失败", [
                        'prize_record_id' => $task->prize_record_id,
                        'error' => $result['message'] ?? '转账失败',
                    ]);
                }

            } catch (\Throwable $e) {
                Log::error("处理任务 {$task->id} 异常: " . $e->getMessage(), [
                    'task_id' => $task->id,
                    'trace' => $e->getTraceAsString()
                ]);

                // 标记为失败
                $this->queueRepository->updateById($task->id, [
                    'status' => QueueConst::STATUS_FAILED,
                    'process_end_time' => now(),
                    'error_message' => $e->getMessage(),
                ]);
            }
        }

        Log::info("PrizeDispatchQueueProcess: 本次处理完成");
    }

    /**
     * 处理超时任务
     */
    protected function handleTimeoutTasks(): void
    {
        // 查找超时任务（处理中超过5分钟）
        $timeoutMinutes = 5;
        $tasks = $this->queueRepository->getTimeoutTasks($timeoutMinutes);

        if ($tasks->isEmpty()) {
            return;
        }

        Log::warning("PrizeDispatchQueueProcess: 发现 {$tasks->count()} 个超时任务");

        foreach ($tasks as $task) {
            try {
                // 重置为待处理状态，增加重试次数
                $this->queueRepository->updateById($task->id, [
                    'status' => QueueConst::STATUS_PENDING,
                    'process_start_time' => null,
                    'error_message' => "任务超时，已重置为待处理",
                ]);

                Log::info("超时任务 {$task->id} 已重置为待处理");

            } catch (\Throwable $e) {
                Log::error("处理超时任务 {$task->id} 失败: " . $e->getMessage());
            }
        }
    }

    /**
     * 重试失败的任务
     */
    protected function retryFailedTasks(): void
    {
        // 获取可重试的失败任务
        $tasks = $this->queueRepository->getRetryableTasks(5);

        if ($tasks->isEmpty()) {
            return;
        }

        Log::info("PrizeDispatchQueueProcess: 准备重试 {$tasks->count()} 个失败任务");

        foreach ($tasks as $task) {
            try {
                // 检查是否超过最大重试次数（使用任务自己的max_retry字段）
                if ($task->retry_count >= $task->max_retry) {
                    Log::warning("任务 {$task->id} 已达到最大重试次数($task->max_retry)，标记为取消");
                    $this->queueRepository->updateById($task->id, [
                        'status' => QueueConst::STATUS_CANCELLED,
                        'error_message' => "超过最大重试次数($task->max_retry)",
                    ]);
                    continue;
                }

                // 重置为待处理状态
                $this->queueRepository->updateById($task->id, [
                    'status' => QueueConst::STATUS_PENDING,
                    'process_start_time' => null,
                ]);

                Log::info("失败任务 {$task->id} 已重置为待处理，第 {$task->retry_count} 次重试");

            } catch (\Throwable $e) {
                Log::error("重试任务 {$task->id} 失败: " . $e->getMessage());
            }
        }
    }
}
