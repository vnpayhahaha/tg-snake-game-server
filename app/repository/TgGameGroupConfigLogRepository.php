<?php

namespace app\repository;

use app\model\ModelTgGameGroupConfigLog;
use DI\Attribute\Inject;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Class TgGameGroupConfigLogRepository.
 * @extends IRepository<ModelTgGameGroupConfigLog>
 */
class TgGameGroupConfigLogRepository extends IRepository
{
    #[Inject]
    protected ModelTgGameGroupConfigLog $model;

    public function handleSearch(Builder $query, array $params): Builder
    {
        if (isset($params['config_id']) && filled($params['config_id'])) {
            $query->where('config_id', $params['config_id']);
        }

        if (isset($params['tg_chat_id']) && filled($params['tg_chat_id'])) {
            $query->where('tg_chat_id', $params['tg_chat_id']);
        }

        if (isset($params['change_source']) && filled($params['change_source'])) {
            $query->where('change_source', $params['change_source']);
        }

        if (isset($params['operator']) && filled($params['operator'])) {
            $query->where('operator', 'like', '%' . $params['operator'] . '%');
        }

        if (isset($params['date_start']) && filled($params['date_start'])) {
            $query->whereDate('created_at', '>=', $params['date_start']);
        }

        if (isset($params['date_end']) && filled($params['date_end'])) {
            $query->whereDate('created_at', '<=', $params['date_end']);
        }

        return $query;
    }

    /**
     * 记录配置变更日志
     */
    public function logConfigChange(array $data): ModelTgGameGroupConfigLog
    {
        return $this->model::query()->create($data);
    }

    /**
     * 获取配置的变更历史
     */
    public function getConfigHistory(int $configId, int $limit = 20): Collection
    {
        return $this->model::query()
            ->where('config_id', $configId)
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * 根据Telegram群组ID获取变更历史
     */
    public function getHistoryByTgChatId(int $tgChatId, int $limit = 20): Collection
    {
        return $this->model::query()
            ->where('tg_chat_id', $tgChatId)
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * 根据变更来源查询
     */
    public function getByChangeSource(int $changeSource, int $limit = 50): Collection
    {
        return $this->model::query()
            ->where('change_source', $changeSource)
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * 根据操作人查询
     */
    public function getByOperator(string $operator, int $limit = 50): Collection
    {
        return $this->model::query()
            ->where('operator', $operator)
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * 获取TG消息相关的变更记录
     */
    public function getByTgMessageId(int $tgChatId, int $tgMessageId): ?ModelTgGameGroupConfigLog
    {
        return $this->model::query()
            ->where('tg_chat_id', $tgChatId)
            ->where('tg_message_id', $tgMessageId)
            ->first();
    }

    /**
     * 统计配置变更次数
     */
    public function countConfigChanges(int $configId, string $dateStart = null, string $dateEnd = null): int
    {
        $query = $this->model::query()
            ->where('config_id', $configId);

        if ($dateStart) {
            $query->whereDate('created_at', '>=', $dateStart);
        }

        if ($dateEnd) {
            $query->whereDate('created_at', '<=', $dateEnd);
        }

        return $query->count();
    }

    /**
     * 统计变更来源分布
     */
    public function getChangeSourceStatistics(int $configId = null): array
    {
        $query = $this->model::query();

        if ($configId !== null) {
            $query->where('config_id', $configId);
        }

        $backendCount = (clone $query)->where('change_source', 1)->count();
        $telegramCount = (clone $query)->where('change_source', 2)->count();

        return [
            'backend' => $backendCount,
            'telegram' => $telegramCount,
            'total' => $backendCount + $telegramCount,
        ];
    }

    /**
     * 获取最近的变更记录
     */
    public function getRecentLogs(int $limit = 10): Collection
    {
        return $this->model::query()
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * 获取指定配置的最后一次变更
     */
    public function getLastChange(int $configId): ?ModelTgGameGroupConfigLog
    {
        return $this->model::query()
            ->where('config_id', $configId)
            ->orderByDesc('created_at')
            ->first();
    }

    /**
     * 清理旧日志（超过指定天数）
     */
    public function cleanOldLogs(int $daysAgo = 180): int
    {
        return $this->model::query()
            ->where('created_at', '<', now()->subDays($daysAgo))
            ->delete();
    }

    /**
     * 获取指定时间段内的变更记录
     */
    public function getChangesByDateRange(int $configId, string $startDate, string $endDate): Collection
    {
        return $this->model::query()
            ->where('config_id', $configId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderByDesc('created_at')
            ->get();
    }
}
