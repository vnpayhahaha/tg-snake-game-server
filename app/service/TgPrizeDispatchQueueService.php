<?php

namespace app\service;

use app\constants\TgPrizeDispatchQueue as QueueConst;
use app\repository\TgPrizeDispatchQueueRepository;
use DI\Attribute\Inject;
use Illuminate\Support\Collection;
use support\Db;
use support\Log;

/**
 * 中奖派发队列服务
 * @extends BaseService
 */
class TgPrizeDispatchQueueService extends BaseService
{
    #[Inject]
    public TgPrizeDispatchQueueRepository $repository;

    /**
     * 创建派奖任务
     */
    public function createTask(array $data)
    {
        return $this->repository->createTask($data);
    }

    /**
     * 获取待处理的任务
     */
    public function getPendingTasks(int $limit = 100): Collection
    {
        return $this->repository->getPendingTasks($limit);
    }

    /**
     * 获取处理中的任务
     */
    public function getProcessingTasks(): Collection
    {
        return $this->repository->getProcessingTasks();
    }

    /**
     * 获取失败需要重试的任务
     */
    public function getFailedTasksForRetry(int $limit = 50): Collection
    {
        return $this->repository->getFailedTasksForRetry($limit);
    }

    /**
     * 根据中奖记录ID获取派奖任务
     */
    public function getByPrizeRecordId(int $prizeRecordId): Collection
    {
        return $this->repository->getByPrizeRecordId($prizeRecordId);
    }

    /**
     * 根据转账ID获取任务
     */
    public function getByPrizeTransferId(int $prizeTransferId)
    {
        return $this->repository->getByPrizeTransferId($prizeTransferId);
    }

    /**
     * 开始处理任务（使用乐观锁）
     */
    public function startTask(int $id): bool
    {
        $task = $this->repository->findById($id);
        if (!$task) {
            return false;
        }

        return $this->repository->startTask($id, $task->version);
    }

    /**
     * 完成任务
     */
    public function completeTask(int $id): bool
    {
        return $this->repository->completeTask($id);
    }

    /**
     * 标记任务失败
     */
    public function failTask(int $id, string $errorMessage): bool
    {
        return $this->repository->failTask($id, $errorMessage);
    }

    /**
     * 取消任务
     */
    public function cancelTask(int $id, string $reason = null): bool
    {
        return $this->repository->cancelTask($id, $reason);
    }

    /**
     * 批量取消任务
     */
    public function batchCancelTasks(array $ids, string $reason = null): int
    {
        return $this->repository->batchCancelTasks($ids, $reason);
    }

    /**
     * 重置失败任务为待处理状态
     */
    public function resetFailedTask(int $id): bool
    {
        return $this->repository->resetFailedTask($id);
    }

    /**
     * 统计任务状态
     */
    public function getTaskStatistics(int $prizeRecordId = null): array
    {
        return $this->repository->getTaskStatistics($prizeRecordId);
    }

    /**
     * 检查中奖记录的所有派奖任务是否完成
     */
    public function isAllTasksCompleted(int $prizeRecordId): bool
    {
        return $this->repository->isAllTasksCompleted($prizeRecordId);
    }

    /**
     * 获取超时的处理中任务
     */
    public function getTimeoutTasks(int $timeoutMinutes = 10): Collection
    {
        return $this->repository->getTimeoutTasks($timeoutMinutes);
    }

    /**
     * 清理旧的已完成任务
     */
    public function cleanOldCompletedTasks(int $daysAgo = 30): int
    {
        return $this->repository->cleanOldCompletedTasks($daysAgo);
    }

    /**
     * 获取延迟执行的任务数量
     */
    public function countDelayedTasks(): int
    {
        return $this->repository->countDelayedTasks();
    }

    /**
     * 分页获取派奖队列（用于Controller）
     */
    public function getDispatchQueuePage(array $params, int $page = 1, int $pageSize = 10): array
    {
        return $this->repository->page($params, $page, $pageSize);
    }

    /**
     * 获取待处理派奖队列（用于Controller）
     */
    public function getPendingDispatchQueues(): Collection
    {
        return $this->getPendingTasks(100);
    }

    /**
     * 获取处理中派奖队列（用于Controller）
     */
    public function getProcessingDispatchQueues(): Collection
    {
        return $this->getProcessingTasks();
    }

    /**
     * 获取失败派奖队列（用于Controller）
     */
    public function getFailedDispatchQueues(): Collection
    {
        $params = ['status' => QueueConst::STATUS_FAILED];
        return $this->repository->list($params);
    }

    /**
     * 根据中奖记录查询派奖队列（用于Controller）
     */
    public function getDispatchQueueByPrizeId(int $prizeId)
    {
        $queues = $this->getByPrizeRecordId($prizeId);
        return $queues->isNotEmpty() ? $queues->first() : null;
    }

    /**
     * 根据群组查询派奖队列
     */
    public function getDispatchQueuesByGroup(int $groupId, int $limit = 50): Collection
    {
        $params = ['group_id' => $groupId];
        return $this->repository->list($params)->take($limit);
    }

    /**
     * 获取派奖队列统计
     */
    public function getDispatchQueueStatistics(int $groupId = null, string $dateStart = null, string $dateEnd = null): array
    {
        $baseStats = $this->getTaskStatistics();

        return array_merge($baseStats, [
            'timeout_count' => $this->getTimeoutTasks()->count(),
            'delayed_count' => $this->countDelayedTasks(),
        ]);
    }

    /**
     * 获取派奖队列详情
     */
    public function getDispatchQueueById(int $id)
    {
        return $this->repository->findById($id);
    }

    /**
     * 获取派奖队列列表
     */
    public function getDispatchQueueList(array $params): Collection
    {
        return $this->repository->list($params);
    }

    /**
     * 手动重试派发
     */
    public function retryDispatchQueue(int $id): array
    {
        try {
            $task = $this->repository->findById($id);
            if (!$task) {
                return [
                    'success' => false,
                    'message' => '派奖任务不存在',
                ];
            }

            if ($task->status === QueueConst::STATUS_COMPLETED) {
                return [
                    'success' => false,
                    'message' => '任务已完成，无需重试',
                ];
            }

            if ($task->retry_count >= $task->max_retry) {
                return [
                    'success' => false,
                    'message' => '已达到最大重试次数',
                ];
            }

            // 重置任务状态
            $this->resetFailedTask($id);

            return [
                'success' => true,
                'message' => '重试成功，任务已重新加入队列',
            ];
        } catch (\Exception $e) {
            Log::error('重试派发任务失败: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => '重试失败: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * 批量重试派发队列
     */
    public function batchRetryDispatchQueues(array $queueIds): array
    {
        $successCount = 0;
        $failedCount = 0;
        $errors = [];

        foreach ($queueIds as $id) {
            $result = $this->retryDispatchQueue($id);
            if ($result['success']) {
                $successCount++;
            } else {
                $failedCount++;
                $errors[] = [
                    'id' => $id,
                    'error' => $result['message'],
                ];
            }
        }

        return [
            'total' => count($queueIds),
            'success' => $successCount,
            'failed' => $failedCount,
            'errors' => $errors,
        ];
    }

    /**
     * 标记派发为成功
     */
    public function markDispatchQueueSuccess(int $id): array
    {
        try {
            $result = $this->completeTask($id);
            return [
                'success' => $result,
                'message' => $result ? '标记成功' : '标记失败',
            ];
        } catch (\Exception $e) {
            Log::error('标记派发成功失败: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * 标记派发为失败
     */
    public function markDispatchQueueFailed(int $id, string $errorMessage): array
    {
        try {
            $result = $this->failTask($id, $errorMessage);
            return [
                'success' => $result,
                'message' => $result ? '标记成功' : '标记失败',
            ];
        } catch (\Exception $e) {
            Log::error('标记派发失败失败: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * 更新派发队列状态
     */
    public function updateDispatchQueueStatus(int $id, int $status): bool
    {
        return $this->repository->updateById($id, ['status' => $status]);
    }

    /**
     * 删除派发队列（软删除）
     */
    public function deleteDispatchQueue($ids): int
    {
        return $this->deleteById($ids);
    }

    /**
     * 真实删除派发队列
     */
    public function realDeleteDispatchQueue(array $ids): bool
    {
        return $this->realDelete($ids);
    }
}
