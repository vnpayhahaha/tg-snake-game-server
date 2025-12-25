<?php

namespace app\repository;

use app\constants\TgPrizeDispatchQueue as QueueConst;
use app\model\ModelTgPrizeDispatchQueue;
use DI\Attribute\Inject;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Class TgPrizeDispatchQueueRepository.
 * @extends IRepository<ModelTgPrizeDispatchQueue>
 */
class TgPrizeDispatchQueueRepository extends IRepository
{
    #[Inject]
    protected ModelTgPrizeDispatchQueue $model;

    public function handleSearch(Builder $query, array $params): Builder
    {
        if (isset($params['prize_record_id']) && filled($params['prize_record_id'])) {
            $query->where('prize_record_id', $params['prize_record_id']);
        }

        if (isset($params['prize_transfer_id']) && filled($params['prize_transfer_id'])) {
            $query->where('prize_transfer_id', $params['prize_transfer_id']);
        }

        if (isset($params['group_id']) && filled($params['group_id'])) {
            $query->where('group_id', $params['group_id']);
        }

        if (isset($params['prize_serial_no']) && filled($params['prize_serial_no'])) {
            $query->where('prize_serial_no', $params['prize_serial_no']);
        }

        if (isset($params['status']) && filled($params['status'])) {
            $query->where('status', $params['status']);
        }

        if (isset($params['priority']) && filled($params['priority'])) {
            $query->where('priority', $params['priority']);
        }

        return $query;
    }

    /**
     * 创建派奖任务
     */
    public function createTask(array $data): ModelTgPrizeDispatchQueue
    {
        return $this->model::query()->create($data);
    }

    /**
     * 获取待处理的任务（按优先级和计划时间排序）
     */
    public function getPendingTasks(int $limit = 100): Collection
    {
        return $this->model::query()
            ->where('status', QueueConst::STATUS_PENDING)
            ->where('scheduled_at', '<=', now())
            ->orderBy('priority')
            ->orderBy('scheduled_at')
            ->limit($limit)
            ->get();
    }

    /**
     * 获取处理中的任务
     */
    public function getProcessingTasks(): Collection
    {
        return $this->model::query()
            ->where('status', QueueConst::STATUS_PROCESSING)
            ->orderBy('started_at')
            ->get();
    }

    /**
     * 获取失败需要重试的任务
     */
    public function getFailedTasksForRetry(int $limit = 50): Collection
    {
        return $this->model::query()
            ->where('status', QueueConst::STATUS_FAILED)
            ->whereColumn('retry_count', '<', 'max_retry')
            ->orderBy('priority')
            ->orderBy('created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * 根据中奖记录ID获取派奖任务
     */
    public function getByPrizeRecordId(int $prizeRecordId): Collection
    {
        return $this->model::query()
            ->where('prize_record_id', $prizeRecordId)
            ->orderBy('priority')
            ->get();
    }

    /**
     * 根据转账ID获取任务
     */
    public function getByPrizeTransferId(int $prizeTransferId): ?ModelTgPrizeDispatchQueue
    {
        return $this->model::query()
            ->where('prize_transfer_id', $prizeTransferId)
            ->first();
    }

    /**
     * 使用乐观锁更新任务状态（开始处理）
     */
    public function startTask(int $id, int $currentVersion): bool
    {
        $affected = $this->model::query()
            ->where('id', $id)
            ->where('version', $currentVersion)
            ->update([
                'status' => QueueConst::STATUS_PROCESSING,
                'started_at' => now(),
                'version' => $currentVersion + 1,
            ]);

        return $affected > 0;
    }

    /**
     * 使用乐观锁更新任务（通用方法）
     */
    public function updateWithVersion(int $id, array $data, int $currentVersion): bool
    {
        // 自动增加版本号
        $data['version'] = $currentVersion + 1;

        $affected = $this->model::query()
            ->where('id', $id)
            ->where('version', $currentVersion)
            ->update($data);

        return $affected > 0;
    }

    /**
     * 完成任务
     */
    public function completeTask(int $id): bool
    {
        return (bool)$this->model::query()
            ->whereKey($id)
            ->update([
                'status' => QueueConst::STATUS_COMPLETED,
                'completed_at' => now(),
            ]);
    }

    /**
     * 标记任务失败并增加重试次数
     */
    public function failTask(int $id, string $errorMessage): bool
    {
        return (bool)$this->model::query()
            ->whereKey($id)
            ->increment('retry_count', 1, [
                'status' => QueueConst::STATUS_FAILED,
                'error_message' => $errorMessage,
            ]);
    }

    /**
     * 取消任务
     */
    public function cancelTask(int $id, string $reason = null): bool
    {
        $data = ['status' => QueueConst::STATUS_CANCELLED];

        if ($reason !== null) {
            $data['error_message'] = $reason;
        }

        return (bool)$this->model::query()
            ->whereKey($id)
            ->update($data);
    }

    /**
     * 批量取消任务
     */
    public function batchCancelTasks(array $ids, string $reason = null): int
    {
        $data = ['status' => QueueConst::STATUS_CANCELLED];

        if ($reason !== null) {
            $data['error_message'] = $reason;
        }

        return $this->model::query()
            ->whereIn('id', $ids)
            ->update($data);
    }

    /**
     * 重置失败任务为待处理状态
     */
    public function resetFailedTask(int $id): bool
    {
        return (bool)$this->model::query()
            ->whereKey($id)
            ->update([
                'status' => QueueConst::STATUS_PENDING,
                'error_message' => null,
            ]);
    }

    /**
     * 统计任务状态
     */
    public function getTaskStatistics(int $prizeRecordId = null): array
    {
        $query = $this->model::query();

        if ($prizeRecordId !== null) {
            $query->where('prize_record_id', $prizeRecordId);
        }

        $pending = (clone $query)->where('status', QueueConst::STATUS_PENDING)->count();
        $processing = (clone $query)->where('status', QueueConst::STATUS_PROCESSING)->count();
        $completed = (clone $query)->where('status', QueueConst::STATUS_COMPLETED)->count();
        $failed = (clone $query)->where('status', QueueConst::STATUS_FAILED)->count();
        $cancelled = (clone $query)->where('status', QueueConst::STATUS_CANCELLED)->count();

        return [
            'pending' => $pending,
            'processing' => $processing,
            'completed' => $completed,
            'failed' => $failed,
            'cancelled' => $cancelled,
            'total' => $pending + $processing + $completed + $failed + $cancelled,
        ];
    }

    /**
     * 检查中奖记录的所有派奖任务是否完成
     */
    public function isAllTasksCompleted(int $prizeRecordId): bool
    {
        $unfinishedCount = $this->model::query()
            ->where('prize_record_id', $prizeRecordId)
            ->whereIn('status', [QueueConst::STATUS_PENDING, QueueConst::STATUS_PROCESSING])
            ->count();

        return $unfinishedCount === 0;
    }

    /**
     * 获取超时的处理中任务（超过指定分钟数）
     */
    public function getTimeoutProcessingTasks(int $timeoutMinutes = 10): Collection
    {
        return $this->model::query()
            ->where('status', QueueConst::STATUS_PROCESSING)
            ->where('started_at', '<', now()->subMinutes($timeoutMinutes))
            ->get();
    }

    /**
     * 获取超时任务（别名）
     */
    public function getTimeoutTasks(int $timeoutMinutes = 10): Collection
    {
        return $this->getTimeoutProcessingTasks($timeoutMinutes);
    }

    /**
     * 获取可重试的失败任务（别名）
     */
    public function getRetryableTasks(int $limit = 50): Collection
    {
        return $this->getFailedTasksForRetry($limit);
    }

    /**
     * 清理旧的已完成任务（超过指定天数）
     */
    public function cleanOldCompletedTasks(int $daysAgo = 30): int
    {
        return $this->model::query()
            ->where('status', QueueConst::STATUS_COMPLETED)
            ->where('completed_at', '<', now()->subDays($daysAgo))
            ->delete();
    }

    /**
     * 获取延迟执行的任务数量
     */
    public function countDelayedTasks(): int
    {
        return $this->model::query()
            ->where('status', QueueConst::STATUS_PENDING)
            ->where('scheduled_at', '>', now())
            ->count();
    }
}
